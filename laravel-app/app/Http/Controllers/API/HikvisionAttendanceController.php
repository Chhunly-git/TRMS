<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AttendanceDeviceLog;
use App\Models\BiometricDevice;
use App\Services\HikvisionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HikvisionAttendanceController extends Controller
{
    protected HikvisionService $hikvisionService;

    public function __construct(HikvisionService $hikvisionService)
    {
        $this->hikvisionService = $hikvisionService;
    }

    /**
     * Webhook Endpoint: Receive HTTP Listening / Event Push from Hikvision Terminals
     * Public Route (Device cannot perform Sanctum token authentication)
     */
    public function handleWebhook(Request $request)
    {
        try {
            $result = $this->hikvisionService->processWebhookEvent($request);

            return response()->json([
                'status' => 'success',
                'statusCode' => 1,
                'statusString' => 'OK',
                'subStatusCode' => 'ok',
                'data' => $result,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Hikvision Webhook Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'statusCode' => 0,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET: List all configured Biometric Devices
     */
    public function getDevices()
    {
        $today = Carbon::today('Asia/Phnom_Penh')->toDateString();

        $devices = BiometricDevice::withCount([
            'logs as today_logs_count' => function ($q) use ($today) {
                $q->whereDate('scan_time', $today);
            }
        ])->orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'devices' => $devices,
        ]);
    }

    /**
     * POST: Add new Biometric Device
     */
    public function storeDevice(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'device_type' => 'nullable|string|max:50',
            'model' => 'nullable|string|max:100',
            'ip_address' => 'required|string|max:100',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:100',
            'password' => 'nullable|string|max:255',
            'protocol' => 'nullable|string|in:HTTP,HTTPS',
            'is_active' => 'boolean',
        ]);

        $validated['device_type'] = $validated['device_type'] ?: 'HIKVISION';
        $validated['protocol'] = strtoupper($validated['protocol'] ?: 'HTTP');

        $device = BiometricDevice::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'បានបន្ថែមម៉ាស៊ីនស្កេនជោគជ័យ!',
            'device' => $device,
        ]);
    }

    /**
     * PUT: Update Biometric Device
     */
    public function updateDevice(Request $request, $id)
    {
        $device = BiometricDevice::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'device_type' => 'nullable|string|max:50',
            'model' => 'nullable|string|max:100',
            'ip_address' => 'required|string|max:100',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:100',
            'password' => 'nullable|string|max:255',
            'protocol' => 'nullable|string|in:HTTP,HTTPS',
            'is_active' => 'boolean',
        ]);

        // If password is left blank, do not overwrite existing password
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $validated['protocol'] = strtoupper($validated['protocol'] ?: 'HTTP');
        $device->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'បានកែប្រែព័ត៌មានម៉ាស៊ីនស្កេនជោគជ័យ!',
            'device' => $device,
        ]);
    }

    /**
     * DELETE: Remove Biometric Device
     */
    public function deleteDevice($id)
    {
        $device = BiometricDevice::findOrFail($id);
        $device->delete();

        return response()->json([
            'success' => true,
            'message' => 'បានលុបម៉ាស៊ីនស្កេនជោគជ័យ!',
        ]);
    }

    /**
     * POST: Test connection to a device via ISAPI
     */
    public function testConnection($id)
    {
        $device = BiometricDevice::findOrFail($id);
        $res = $this->hikvisionService->testDeviceConnection($device);

        return response()->json($res);
    }

    /**
     * POST: Trigger manual sync of attendance events for a date
     */
    public function syncDevice(Request $request, $id)
    {
        $device = BiometricDevice::findOrFail($id);
        $date = $request->input('date', Carbon::today('Asia/Phnom_Penh')->toDateString());

        $res = $this->hikvisionService->syncDeviceEvents($device, $date);

        return response()->json($res);
    }

    /**
     * GET: Query Device Scan Logs (Real-time Audit Trail)
     */
    public function getDeviceLogs(Request $request)
    {
        $date = $request->input('date');
        $status = $request->input('status'); // PROCESSED, UNMATCHED
        $search = $request->input('search');

        $query = AttendanceDeviceLog::with(['user:id,name,name_kh,profile_image,position_id', 'user.position:id,title_kh', 'device:id,name,ip_address'])
            ->orderBy('id', 'desc');

        if ($date) {
            $query->whereDate('scan_time', $date);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('employee_no', 'like', "%{$search}%")
                  ->orWhere('card_no', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('name_kh', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->paginate(30);

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    /**
     * GET: Setup Information and Webhook URL Guide
     */
    public function getSetupInfo(Request $request)
    {
        $serverHost = $request->getHttpHost();
        $scheme = $request->getScheme();
        $webhookUrl = "{$scheme}://{$serverHost}/api/attendance/hikvision/event";

        return response()->json([
            'success' => true,
            'webhook_url' => $webhookUrl,
            'server_ip' => $request->server('SERVER_ADDR') ?: gethostbyname(gethostname()),
            'server_port' => $request->server('SERVER_PORT') ?: 80,
            'supported_methods' => [
                'Real-Time HTTP Listening (Event Push)',
                'ISAPI AcsEvent Query (Manual / Scheduled Sync)',
            ],
        ]);
    }
}
