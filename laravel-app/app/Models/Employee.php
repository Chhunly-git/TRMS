<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Casts សម្រាប់ JSON និង Date fields
     */
    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'pob' => 'array',
            'current_address' => 'array',
            'additional_info' => 'array',
        ];
    }

    /**
     * ទំនាក់ទំនងទៅកាន់ User Account
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ទំនាក់ទំនងទៅកាន់ Department, Office, Position
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Accessor ទាញយករូបថត Profile ពីតារាង User
     */
    protected function profileImage(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->user?->profile_image ?? asset('images/default-avatar.png'),
        );
    }
}