<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkingGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name_kh',
        'name_en',
        'code',
        'reference_document',
        'established_date',
        'description',
        'status',
        'order_index',
        'created_by',
    ];

    protected $casts = [
        'established_date' => 'date',
        'order_index' => 'integer',
    ];

    protected $appends = [
        'leader_name_kh',
        'deputy_leaders_count',
        'members_total_count',
    ];

    /**
     * សមាជិកទាំងអស់ក្នុងក្រុមការងារ
     */
    public function members(): HasMany
    {
        return $this->hasMany(WorkingGroupMember::class, 'working_group_id')
            ->orderBy('order_index', 'asc')
            ->orderBy('id', 'asc');
    }

    /**
     * Users ក្នុងក្រុមការងារ (many-to-many)
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'working_group_members')
            ->withPivot(['id', 'role', 'custom_role_kh', 'order_index', 'status', 'joined_date', 'notes'])
            ->withTimestamps();
    }

    /**
     * អ្នកបង្កើត
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * ឯកសារចាត់ចែងមកកាន់ក្រុមការងារនេះ
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(InboundDocumentAssignment::class, 'working_group_id');
    }

    /**
     * ប្រធានក្រុមការងារ
     */
    public function getLeaderAttribute()
    {
        return $this->members->first(fn($m) => $m->role === 'LEADER' && $m->status === 'ACTIVE');
    }

    /**
     * ឈ្មោះប្រធានក្រុមការងារ
     */
    public function getLeaderNameKhAttribute(): string
    {
        $leader = $this->leader;
        if (!$leader || !$leader->user) {
            return 'មិនទាន់កំណត់';
        }
        return $leader->user->name_kh ?? $leader->user->name ?? '';
    }

    /**
     * ចំនួនអនុប្រធានក្រុមការងារ
     */
    public function getDeputyLeadersCountAttribute(): int
    {
        return $this->members->filter(fn($m) => $m->role === 'DEPUTY_LEADER' && $m->status === 'ACTIVE')->count();
    }

    /**
     * ចំនួនសមាជិកសរុប
     */
    public function getMembersTotalCountAttribute(): int
    {
        return $this->members->filter(fn($m) => $m->status === 'ACTIVE')->count();
    }
}
