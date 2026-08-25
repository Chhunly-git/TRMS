<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Services\ImageClassService;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'name_kh',
        'name_en',
        'email',
        'password',
        'profile_image',
        'level',
        'status',
        'department_id',
        'office_id',
        'position_id',
        'employee_code',
        'mef_card_number',
        'employee_type',
        'gender',
        'marital_status',
        'dob',
        'birth_place',
        'current_address',
        'phone',
        'national_id_number',
        'national_id_expired_date',
        'passport_number',
        'passport_expired_date',
        // ១. ព័ត៌មានបម្រើการងាររដ្ឋដំបូង (បន្ថែមថ្មី)
    'first_service_date',
    'first_appointment_date',
    'initial_framework',
    'initial_position',
    'initial_ministry',
    'initial_unit',
    'initial_department',
    'initial_office',

    // ២. ស្ថានភាពមុខងារបច្ចុប្បន្ន (បន្ថែមថ្មី)
    'current_framework',
    'current_appointment_date',
    'current_position_date',
    
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'dob' => 'date',
            'national_id_expired_date' => 'date',
            'passport_expired_date' => 'date',
        ];
    }

    /**
     * ទំនាក់ទំនងទៅកាន់តារាង Department (នាយកដ្ឋាន)
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * ទំនាក់ទំនងទៅកាន់តារាង Office (ការិយាល័យ)
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    /**
     * ទំនាក់ទំនងទៅកាន់តារាង Position (តួនាទី)
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    /**
     * Send Password Reset Notification
     */
    public function sendPasswordResetNotification($token, $callback_url = null)
    {
        $this->notify(new ResetPasswordNotification($token, $callback_url));
    }

    /**
     * ពិនិត្យមើលថាតើ Password ទំនេរឬអត់
     */
    protected function passwordNull(): Attribute
    {
        return Attribute::make(
            get: fn() => empty($this->password),
        );
    }

    /**
     * ទាញយក Full URL នៃ Profile Image (Safe Null Checking)
     */
    protected function profileImage(): Attribute
    {
        return Attribute::make(
            get: function () {
                $imagePath = $this->getRawOriginal('profile_image');
                if (empty($imagePath)) {
                    return null;
                }

                try {
                    $imageClass = ImageClassService::forUserModel();
                    return $imageClass ? $imageClass->fullUrl($imagePath) : $imagePath;
                } catch (\Throwable $e) {
                    return $imagePath;
                }
            },
        );
    }

    /**
     * ទាញយក Full URL នៃ Profile Thumbnail (Safe Null Checking)
     */
    protected function profileThumbnail(): Attribute
    {
        return Attribute::make(
            get: function () {
                $imagePath = $this->getRawOriginal('profile_image');
                if (empty($imagePath)) {
                    return null;
                }

                try {
                    $imageClass = ImageClassService::forUserModel();
                    if ($imageClass) {
                        $thumbnailPath = $imageClass->thumbnailPath($imagePath);
                        return $imageClass->fullUrl($thumbnailPath);
                    }
                } catch (\Throwable $e) {
                    // Fallback បើ ImageClassService មានបញ្ហា
                }

                return str_replace('users/profile-images/', 'users/profile-images/thumbnails/', $imagePath);
            },
        );
    }

    // --- Query Scopes ---

    protected function scopeIsAdmin(Builder $query): void
    {
        $query->where('level', 'ADMIN');
    }

    protected function scopeIsUser(Builder $query): void
    {
        $query->where('level', 'USER');
    }

    protected function scopeIsEnabled(Builder $query): void
    {
        $query->where('status', 'ENABLED');
    }

    protected function scopeIsDisabled(Builder $query): void
    {
        $query->where('status', 'DISABLED');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'user_id');
    }
}