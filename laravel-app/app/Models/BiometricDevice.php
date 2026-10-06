<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiometricDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'device_type',
        'model',
        'serial_number',
        'ip_address',
        'port',
        'username',
        'password',
        'protocol',
        'is_active',
        'last_sync_at',
        'last_status',
        'status_message',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
        'port' => 'integer',
    ];

    /**
     * Relationship to raw logs received from this device
     */
    public function logs()
    {
        return $this->hasMany(AttendanceDeviceLog::class, 'device_id');
    }

    /**
     * Get Base URL for ISAPI queries
     */
    public function getBaseUrlAttribute(): string
    {
        $proto = strtolower($this->protocol ?: 'http');
        $ip = $this->ip_address ?: '127.0.0.1';
        $port = $this->port ?: 80;

        return "{$proto}://{$ip}:{$port}";
    }
}
