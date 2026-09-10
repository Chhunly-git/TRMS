<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeetingRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'capacity',
        'facilities',
        'color',
        'manager_id',
        'status',
        'description',
        'image',
    ];

    protected $appends = [
        'status_khmer',
        'facilities_list',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
        ];
    }

    /**
     * ទំនាក់ទំនងទៅកាន់ User ដែលជាអ្នកទទួលខុសត្រូវបន្ទប់ (Room Manager)
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * ទំនាក់ទំនងទៅកាន់បញ្ជីកក់របស់បន្ទប់នេះ
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(RoomBooking::class, 'room_id');
    }

    /**
     * បកប្រែស្ថានភាពបន្ទប់ជាភាសាខ្មែរ
     */
    protected function statusKhmer(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->status) {
                    'ACTIVE' => 'កំពុងដំណើរការ',
                    'MAINTENANCE' => 'កំពុងជួសជុល',
                    'INACTIVE' => 'ផ្អាកដំណើរការ',
                    default => $this->status ?? 'កំពុងដំណើរការ',
                };
            }
        );
    }

    /**
     * បំលែង facilities ទៅជា Array
     */
    protected function facilitiesList(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (empty($this->facilities)) {
                    return [];
                }
                if (is_array($this->facilities)) {
                    return $this->facilities;
                }
                $decoded = json_decode($this->facilities, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
                return array_map('trim', explode(',', $this->facilities));
            }
        );
    }

    /**
     * Scope សម្រាប់ទាញយកបន្ទប់ដែលកំពុងដំណើរការ
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }
}
