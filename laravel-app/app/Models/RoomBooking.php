<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'preferred_room_id',
        'room_id',
        'title',
        'leader_name',
        'booking_date',
        'start_time',
        'end_time',
        'start_datetime',
        'end_datetime',
        'participants_count',
        'equipment_needed',
        'description',
        'status',
        'approved_by',
        'approved_at',
        'manager_note',
        'rejection_reason',
    ];

    protected $appends = [
        'subject',
        'leader',
        'status_khmer',
        'formatted_time_range',
        'required_equipment',
        'admin_note',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'start_datetime' => 'datetime',
            'end_datetime' => 'datetime',
            'approved_at' => 'datetime',
            'participants_count' => 'integer',
        ];
    }

    public function getSubjectAttribute(): ?string
    {
        return $this->title;
    }

    public function setSubjectAttribute($value): void
    {
        $this->attributes['title'] = $value;
    }

    public function getLeaderAttribute(): ?string
    {
        return $this->leader_name;
    }

    public function setLeaderAttribute($value): void
    {
        $this->attributes['leader_name'] = $value;
    }

    public function getAdminNoteAttribute(): ?string
    {
        return $this->manager_note;
    }

    public function setAdminNoteAttribute($value): void
    {
        $this->attributes['manager_note'] = $value;
    }

    public function getNotesAttribute(): ?string
    {
        return $this->description;
    }

    public function setNotesAttribute($value): void
    {
        $this->attributes['description'] = $value;
    }

    public function getRequiredEquipmentAttribute(): array
    {
        if (empty($this->equipment_needed)) {
            return [];
        }
        if (is_array($this->equipment_needed)) {
            return $this->equipment_needed;
        }
        $decoded = json_decode($this->equipment_needed, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        return array_map('trim', explode(',', $this->equipment_needed));
    }

    public function setRequiredEquipmentAttribute($value): void
    {
        if (is_array($value)) {
            $this->attributes['equipment_needed'] = json_encode($value, JSON_UNESCAPED_UNICODE);
        } else {
            $this->attributes['equipment_needed'] = $value;
        }
    }

    /**
     * ទំនាក់ទំនងទៅកាន់មន្ត្រីអ្នកស្នើសុំ (Applicant)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * ទំនាក់ទំនងទៅកាន់បន្ទប់ប្រជុំដែលមន្ត្រីបានចង់បាន (Preferred Room)
     */
    public function preferredRoom(): BelongsTo
    {
        return $this->belongsTo(MeetingRoom::class, 'preferred_room_id');
    }

    /**
     * ទំនាក់ទំនងទៅកាន់បន្ទប់ប្រជុំដែលអ្នកគ្រប់គ្រងបានកំណត់ជូន (Assigned Room)
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(MeetingRoom::class, 'room_id');
    }

    /**
     * ទំនាក់ទំនងទៅកាន់អ្នកគ្រប់គ្រងដែលបានអនុម័ត/ចាត់តាំងបន្ទប់
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * បកប្រែស្ថានភាពជាភាសាខ្មែរ
     */
    protected function statusKhmer(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->status) {
                    'PENDING' => 'រង់ចាំការចាត់ចែង',
                    'APPROVED' => 'បានអនុម័ត & កំណត់បន្ទប់',
                    'REJECTED' => 'បានបដិសេធ',
                    'CANCELLED' => 'បានបោះបង់',
                    default => $this->status ?? 'រង់ចាំការចាត់ចែង',
                };
            }
        );
    }

    /**
     * ទម្រង់ម៉ោងប្រជុំងាយស្រួលមើល ឧ. 09:00 - 11:30
     */
    protected function formattedTimeRange(): Attribute
    {
        return Attribute::make(
            get: function () {
                return "{$this->start_time} - {$this->end_time}";
            }
        );
    }

    /**
     * Scope សម្រាប់ស្វែងរកការកក់ដែលជាន់ម៉ោងគ្នា (Conflict Detection)
     */
    public function scopeConflictingWith($query, $roomId, $startDatetime, $endDatetime, $excludeBookingId = null)
    {
        return $query->where('room_id', $roomId)
            ->where('status', 'APPROVED')
            ->where(function ($q) use ($startDatetime, $endDatetime) {
                $q->where('start_datetime', '<', $endDatetime)
                  ->where('end_datetime', '>', $startDatetime);
            })
            ->when($excludeBookingId, function ($q) use ($excludeBookingId) {
                $q->where('id', '!=', $excludeBookingId);
            });
    }
}
