<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkingGroupMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'working_group_id',
        'user_id',
        'role',
        'custom_role_kh',
        'joined_date',
        'status',
        'order_index',
        'notes',
    ];

    protected $casts = [
        'joined_date' => 'date',
        'order_index' => 'integer',
    ];

    protected $appends = [
        'role_kh',
    ];

    public function workingGroup(): BelongsTo
    {
        return $this->belongsTo(WorkingGroup::class, 'working_group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getRoleKhAttribute(): string
    {
        if (!empty($this->custom_role_kh)) {
            return $this->custom_role_kh;
        }

        return match ($this->role) {
            'LEADER' => 'ប្រធានក្រុមការងារ',
            'DEPUTY_LEADER' => 'អនុប្រធានក្រុមការងារ',
            'SECRETARY' => 'លេខាធិការ',
            'PERMANENT_MEMBER' => 'សមាជិកអចិន្ត្រៃយ៍',
            'MEMBER' => 'សមាជិក',
            default => $this->role ?: 'សមាជិក',
        };
    }
}
