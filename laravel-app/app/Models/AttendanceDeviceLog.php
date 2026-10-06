<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceDeviceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'user_id',
        'employee_no',
        'card_no',
        'scan_time',
        'verify_mode',
        'event_type',
        'ip_address',
        'status',
        'note',
        'raw_payload',
    ];

    protected $casts = [
        'scan_time' => 'datetime',
        'raw_payload' => 'array',
    ];

    public function device()
    {
        return $this->belongsTo(BiometricDevice::class, 'device_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
