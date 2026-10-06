<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceDeviceLog;
use App\Models\BiometricDevice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HikvisionService
{
    /**
     * Process an incoming HTTP event / Webhook from a HIKVISION device
     * Supports both raw JSON and multipart/form-data
     */
    public function processWebhookEvent(Request $request): array
    {
        $rawEvent = $this->extractEventData($request);

        if (!$rawEvent) {
            return [
                'success' => false,
                'message' => 'No recognizable Hikvision event payload found.',
                'code' => 400
            ];
        }

        // Extract event fields
        $deviceIp = $rawEvent['ipAddress'] ?? $request->ip();
        $dateTimeStr = $rawEvent['dateTime'] ?? Carbon::now('Asia/Phnom_Penh')->toIso8601String();
        
        // In Hikvision ISAPI, event details can be at top-level or under AccessControllerEvent
        $accessEvent = $rawEvent['AccessControllerEvent'] ?? $rawEvent;
        
        $employeeNo = trim((string) ($accessEvent['employeeNoString'] ?? $accessEvent['employeeNo'] ?? ''));
        $cardNo = trim((string) ($accessEvent['cardNo'] ?? ''));
        $verifyMode = strtolower((string) ($accessEvent['currentVerifyMode'] ?? $accessEvent['verifyMode'] ?? 'unknown'));
        $eventType = (string) ($accessEvent['attendanceStatus'] ?? $rawEvent['eventType'] ?? 'scan');
        $deviceName = (string) ($accessEvent['deviceName'] ?? $rawEvent['eventDescription'] ?? 'Hikvision Terminal');

        // Associate device if exists by IP
        $device = BiometricDevice::where('ip_address', $deviceIp)->first();
        if (!$device && $deviceIp) {
            // Find device where IP might be in private/local range or matches
            $device = BiometricDevice::where('is_active', true)->first();
        }

        // Try to match officer / user
        $user = $this->findMatchingUser($employeeNo, $cardNo);

        $scanDateTime = $this->parseDateTime($dateTimeStr);
        $scanDate = $scanDateTime->toDateString();
        $scanTime = $scanDateTime->format('H:i');

        // Save raw log
        $logStatus = $user ? 'PROCESSED' : 'UNMATCHED';
        $logNote = $user ? "ផ្គូផ្គងជាមួយ: {$user->name_kh} ({$user->id})" : "មិនអាចស្វែងរកមន្ត្រីដែលមានកូដ: {$employeeNo}";

        $deviceLog = AttendanceDeviceLog::create([
            'device_id' => $device?->id,
            'user_id' => $user?->id,
            'employee_no' => $employeeNo ?: 'UNKNOWN',
            'card_no' => $cardNo ?: null,
            'scan_time' => $scanDateTime->toDateTimeString(),
            'verify_mode' => $verifyMode,
            'event_type' => $eventType,
            'ip_address' => $deviceIp,
            'status' => $logStatus,
            'note' => $logNote,
            'raw_payload' => $rawEvent,
        ]);

        if ($device) {
            $device->update([
                'last_sync_at' => Carbon::now('Asia/Phnom_Penh'),
                'last_status' => 'ONLINE',
                'status_message' => "បានទទួល Event ពីមន្ត្រី [{$employeeNo}] នៅម៉ោង " . Carbon::now('Asia/Phnom_Penh')->format('H:i:s'),
            ]);
        }

        // If user matched, update or create Attendance record
        $attendanceRecord = null;
        if ($user) {
            $attendanceRecord = $this->recordAttendance($user, $scanDate, $scanTime, $verifyMode, $device?->name ?: $deviceName);
        }

        return [
            'success' => true,
            'message' => $user ? 'Attendance recorded successfully' : 'Event logged but user not matched',
            'user_id' => $user?->id,
            'user_name' => $user?->name_kh ?: $user?->name,
            'employee_no' => $employeeNo,
            'scan_time' => "{$scanDate} {$scanTime}",
            'status' => $logStatus,
            'attendance_id' => $attendanceRecord?->id,
        ];
    }

    /**
     * Record or update daily attendance for the user
     */
    public function recordAttendance(User $user, string $date, string $time, string $verifyMode = 'device', string $source = 'Hikvision'): Attendance
    {
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->first();

        if (!$attendance) {
            // First scan of the day -> Check In
            $status = ($time > '09:00') ? 'LATE' : 'PRESENT';

            return Attendance::create([
                'user_id' => $user->id,
                'date' => $date,
                'status' => $status,
                'check_in_time' => $time,
                'check_out_time' => null,
                'note' => "Scan ពីម៉ាស៊ីន ({$verifyMode})",
            ]);
        }

        // If attendance already exists
        $currentCheckIn = $attendance->check_in_time ? Carbon::parse($attendance->check_in_time)->format('H:i') : null;
        $currentCheckOut = $attendance->check_out_time ? Carbon::parse($attendance->check_out_time)->format('H:i') : null;

        if (!$currentCheckIn) {
            // No check-in time yet
            $status = ($time > '09:00') ? 'LATE' : 'PRESENT';
            $attendance->update([
                'check_in_time' => $time,
                'status' => $attendance->status === 'PRESENT' ? $status : $attendance->status,
            ]);
            return $attendance;
        }

        // If scan is earlier than current check-in time, update check-in
        if ($time < $currentCheckIn) {
            $newStatus = ($time > '09:00') ? 'LATE' : 'PRESENT';
            $attendance->update([
                'check_in_time' => $time,
                'status' => in_array($attendance->status, ['PRESENT', 'LATE']) ? $newStatus : $attendance->status,
            ]);
            return $attendance;
        }

        // If scan is later than current check-in time
        $checkInCarbon = Carbon::parse("{$date} {$currentCheckIn}");
        $scanCarbon = Carbon::parse("{$date} {$time}");
        $diffMinutes = $scanCarbon->diffInMinutes($checkInCarbon);

        // Only count as check-out if at least 10 minutes after check-in
        if ($diffMinutes >= 10) {
            $attendance->update([
                'check_out_time' => $time,
            ]);
        }

        return $attendance;
    }

    /**
     * Find matching user by employeeNoString or cardNo
     */
    public function findMatchingUser(?string $employeeNo, ?string $cardNo = null): ?User
    {
        if (empty($employeeNo) && empty($cardNo)) {
            return null;
        }

        // 1. Match by employee_code (Exact)
        if (!empty($employeeNo)) {
            $user = User::where('employee_code', $employeeNo)
                ->where('status', '!=', 'DISABLED')
                ->first();
            if ($user) return $user;

            // 1b. Match employee_code without leading zeros or spaces
            $cleanedCode = ltrim($employeeNo, '0');
            if ($cleanedCode !== $employeeNo && !empty($cleanedCode)) {
                $user = User::where('employee_code', $cleanedCode)
                    ->where('status', '!=', 'DISABLED')
                    ->first();
                if ($user) return $user;
            }

            // 2. Match by User ID
            if (is_numeric($employeeNo)) {
                $user = User::where('id', (int) $employeeNo)
                    ->where('status', '!=', 'DISABLED')
                    ->first();
                if ($user) return $user;
            }

            // 3. Match employeeNo against mef_card_number
            $user = User::where('mef_card_number', $employeeNo)
                ->where('status', '!=', 'DISABLED')
                ->first();
            if ($user) return $user;
        }

        // 4. Match by cardNo
        if (!empty($cardNo)) {
            $user = User::where('mef_card_number', $cardNo)
                ->where('status', '!=', 'DISABLED')
                ->first();
            if ($user) return $user;

            $user = User::where('employee_code', $cardNo)
                ->where('status', '!=', 'DISABLED')
                ->first();
            if ($user) return $user;
        }

        return null;
    }

    /**
     * Test connection to a Hikvision device via ISAPI
     */
    public function testDeviceConnection(BiometricDevice $device): array
    {
        $baseUrl = $device->base_url;
        $username = $device->username ?: 'admin';
        $password = $device->password ?: '';

        try {
            // Query Device Information
            $response = Http::withDigestAuth($username, $password)
                ->timeout(6)
                ->get("{$baseUrl}/ISAPI/System/deviceInfo");

            if ($response->successful()) {
                $body = $response->body();
                
                // Parse XML or JSON
                $model = null;
                $serial = null;
                $deviceName = null;

                if (str_starts_with(trim($body), '{')) {
                    $json = json_decode($body, true);
                    $model = $json['DeviceInfo']['model'] ?? null;
                    $serial = $json['DeviceInfo']['serialNumber'] ?? null;
                    $deviceName = $json['DeviceInfo']['deviceName'] ?? null;
                } else {
                    // XML response
                    try {
                        $xml = simplexml_load_string($body);
                        if ($xml) {
                            $model = (string) ($xml->model ?? '');
                            $serial = (string) ($xml->serialNumber ?? '');
                            $deviceName = (string) ($xml->deviceName ?? '');
                        }
                    } catch (\Exception $e) {}
                }

                $device->update([
                    'last_status' => 'ONLINE',
                    'last_sync_at' => Carbon::now('Asia/Phnom_Penh'),
                    'model' => $model ?: $device->model,
                    'serial_number' => $serial ?: $device->serial_number,
                    'status_message' => 'បានតភ្ជាប់ជោគជ័យ (Connected)',
                ]);

                return [
                    'success' => true,
                    'status' => 'ONLINE',
                    'message' => 'បានតភ្ជាប់ទៅកាន់ម៉ាស៊ីន HIKVISION ដោយជោគជ័យ!',
                    'device_info' => [
                        'name' => $deviceName ?: $device->name,
                        'model' => $model ?: $device->model,
                        'serial_number' => $serial ?: $device->serial_number,
                    ],
                ];
            }

            $device->update([
                'last_status' => 'OFFLINE',
                'status_message' => "កំហុស HTTP Status: {$response->status()}",
            ]);

            return [
                'success' => false,
                'status' => 'OFFLINE',
                'message' => "មិនអាចតភ្ជាប់បានទេ (HTTP Error: {$response->status()})។ សូមពិនិត្យ IP, Port, Username និង Password។",
            ];
        } catch (\Exception $e) {
            $device->update([
                'last_status' => 'OFFLINE',
                'status_message' => 'កំហុសតភ្ជាប់: ' . $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 'OFFLINE',
                'message' => 'បរាជ័យក្នុងការតភ្ជាប់ទៅកាន់ម៉ាស៊ីន: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Sync attendance logs from Hikvision device for a given date via ISAPI
     */
    public function syncDeviceEvents(BiometricDevice $device, string $date): array
    {
        $baseUrl = $device->base_url;
        $username = $device->username ?: 'admin';
        $password = $device->password ?: '';

        $startTime = "{$date}T00:00:00+07:00";
        $endTime = "{$date}T23:59:59+07:00";

        $searchPayload = [
            'AcsEventCond' => [
                'searchID' => '1',
                'searchResultPosition' => 0,
                'maxResults' => 200,
                'major' => 5, // Access control event
                'sub' => 75,  // Authentication passed
                'startTime' => $startTime,
                'endTime' => $endTime,
            ],
        ];

        try {
            $response = Http::withDigestAuth($username, $password)
                ->timeout(12)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$baseUrl}/ISAPI/AccessControl/AcsEvent?format=json", $searchPayload);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => "ម៉ាស៊ីនឆ្លើយតបបរាជ័យ (HTTP: {$response->status()})",
                ];
            }

            $data = $response->json();
            $infoList = $data['AcsEvent']['InfoList'] ?? [];

            $processedCount = 0;
            $matchedCount = 0;

            foreach ($infoList as $item) {
                $employeeNo = trim((string) ($item['employeeNoString'] ?? $item['employeeNo'] ?? ''));
                if (empty($employeeNo)) continue;

                $cardNo = trim((string) ($item['cardNo'] ?? ''));
                $timeStr = (string) ($item['time'] ?? "{$date}T08:00:00+07:00");
                $verifyMode = (string) ($item['currentVerifyMode'] ?? 'device');

                $scanDateTime = $this->parseDateTime($timeStr);
                $scanDate = $scanDateTime->toDateString();
                $scanTime = $scanDateTime->format('H:i');

                $user = $this->findMatchingUser($employeeNo, $cardNo);

                AttendanceDeviceLog::create([
                    'device_id' => $device->id,
                    'user_id' => $user?->id,
                    'employee_no' => $employeeNo,
                    'card_no' => $cardNo ?: null,
                    'scan_time' => $scanDateTime->toDateTimeString(),
                    'verify_mode' => $verifyMode,
                    'event_type' => 'ISAPI_SYNC',
                    'ip_address' => $device->ip_address,
                    'status' => $user ? 'PROCESSED' : 'UNMATCHED',
                    'note' => $user ? "Sync ដោយជោគជ័យ: {$user->name_kh}" : "Sync រួច តែមិនមានមន្ត្រីកូដ {$employeeNo}",
                    'raw_payload' => $item,
                ]);

                if ($user) {
                    $this->recordAttendance($user, $scanDate, $scanTime, $verifyMode, $device->name);
                    $matchedCount++;
                }

                $processedCount++;
            }

            $device->update([
                'last_status' => 'ONLINE',
                'last_sync_at' => Carbon::now('Asia/Phnom_Penh'),
                'status_message' => "បាន Sync ជោគជ័យចំនួន {$matchedCount} នាក់ សម្រាប់ថ្ងៃ {$date}",
            ]);

            return [
                'success' => true,
                'message' => "បានទាញយកទិន្នន័យពីម៉ាស៊ីនដោយជោគជ័យចំនួន {$processedCount} កំណត់ត្រា (ផ្គូផ្គងត្រូវ {$matchedCount} នាក់)",
                'processed_count' => $processedCount,
                'matched_count' => $matchedCount,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'កំហុសពេលទាញយកទិន្នន័យ: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Helper to extract event data from JSON or multipart
     */
    protected function extractEventData(Request $request): ?array
    {
        // 1. Plain JSON request
        if ($request->isJson()) {
            return $request->json()->all();
        }

        // 2. Check for multipart/form-data field 'event_log' or 'json'
        $eventJson = $request->input('event_log') ?: $request->input('json') ?: $request->input('event');
        if ($eventJson) {
            $decoded = json_decode($eventJson, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // 3. Raw Body
        $rawContent = $request->getContent();
        if (!empty($rawContent)) {
            // Check if raw body is JSON
            if (str_starts_with(trim($rawContent), '{')) {
                $decoded = json_decode($rawContent, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }

            // Multipart raw parsing if boundary exists
            if (str_contains($rawContent, 'Content-Disposition: form-data; name="event_log"') || str_contains($rawContent, 'name="event_log"')) {
                preg_match('/name="event_log"[^\r\n]*\r?\n\r?\n(.*?)\r?\n--/s', $rawContent, $matches);
                if (!empty($matches[1])) {
                    $decoded = json_decode(trim($matches[1]), true);
                    if (is_array($decoded)) {
                        return $decoded;
                    }
                }
            }
        }

        // 4. Standard Form inputs
        $all = $request->all();
        if (!empty($all) && (isset($all['employeeNoString']) || isset($all['AccessControllerEvent']))) {
            return $all;
        }

        return null;
    }

    /**
     * Parse date string into Carbon Asia/Phnom_Penh
     */
    protected function parseDateTime(string $dateTimeStr): Carbon
    {
        try {
            return Carbon::parse($dateTimeStr)->setTimezone('Asia/Phnom_Penh');
        } catch (\Exception $e) {
            return Carbon::now('Asia/Phnom_Penh');
        }
    }
}
