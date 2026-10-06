<?php

namespace App\Console\Commands;

use App\Models\BiometricDevice;
use App\Services\HikvisionService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncHikvisionAttendanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:sync-hikvision {--device= : Specific Device ID} {--date= : Date in YYYY-MM-DD}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync attendance records from HIKVISION biometric terminals via ISAPI';

    /**
     * Execute the console command.
     */
    public function handle(HikvisionService $hikvisionService)
    {
        $date = $this->option('date') ?: Carbon::today('Asia/Phnom_Penh')->toDateString();
        $deviceId = $this->option('device');

        $query = BiometricDevice::where('is_active', true);
        if ($deviceId) {
            $query->where('id', $deviceId);
        }

        $devices = $query->get();

        if ($devices->isEmpty()) {
            $this->warn('No active HIKVISION devices found.');
            return 0;
        }

        $this->info("Starting HIKVISION attendance sync for date: {$date}...");

        foreach ($devices as $device) {
            $this->line("Syncing device: [{$device->id}] {$device->name} ({$device->ip_address})...");
            $result = $hikvisionService->syncDeviceEvents($device, $date);

            if ($result['success']) {
                $this->info(" -> {$result['message']}");
            } else {
                $this->error(" -> Failed: {$result['message']}");
            }
        }

        $this->info('Sync completed successfully.');
        return 0;
    }
}
