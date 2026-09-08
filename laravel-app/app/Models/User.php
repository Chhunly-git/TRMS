<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Services\ImageClassService;
use Carbon\Carbon;
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
        'permissions',
        'status',
        'department_id',
        'office_id',
        'position_id',
        'employee_code',
        'mef_card_number',
        'employee_type',
        'officer_status',
        'officer_status_date',
        'officer_status_reason',
        'gender',
        'marital_status',
        'dob',
        'birth_place',
        'current_address',
        'phone',
        'national_id_number',
        'national_id_expired_date',
        'national_id_file',
        'passport_number',
        'passport_expired_date',
        'passport_file',
        // ១. ព័ត៌មានបម្រើការងាររដ្ឋដំបូង (បន្ថែមថ្មី)
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

    // ៧. ស្ថានភាពគ្រួសារ (ឪពុកម្តាយ និង សហព័ទ្ធ)
    'father_name', 'father_latin_name', 'father_status', 'father_dob', 'father_nationality', 'father_address', 'father_occupation', 'father_unit',
    'mother_name', 'mother_latin_name', 'mother_status', 'mother_dob', 'mother_nationality', 'mother_address', 'mother_occupation', 'mother_unit',
    'spouse_name', 'spouse_latin_name', 'spouse_status', 'spouse_dob', 'spouse_nationality', 'spouse_birthplace', 'spouse_occupation', 'spouse_unit', 'spouse_allowance', 'spouse_phone',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'service_duration',
        'service_duration_formatted',
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
            'first_service_date' => 'date',
            'first_appointment_date' => 'date',
            'current_appointment_date' => 'date',
            'current_position_date' => 'date',
            'officer_status_date' => 'date',
            'permissions' => 'array',
        ];
    }

    /**
     * ត្រួតពិនិត្យសិទ្ធិប្រើប្រាស់ម៉ឺនុយ ឬមុខងារនីមួយៗ
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->level === 'ADMIN') {
            return true;
        }

        // ប្រសិនបើសិទ្ធិជា null ឬទទេ ផ្តល់សិទ្ធិលំនាំដើមរបស់មន្ត្រីទូទៅ
        $perms = $this->permissions;
        if (empty($perms) || !is_array($perms)) {
            return in_array($permission, ['profile', 'my-attendances', 'document-templates']);
        }

        return in_array($permission, $perms);
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

    public function additionalPositions()
    {
        return $this->hasMany(AdditionalPosition::class, 'user_id');
    }

    public function outOfFrameworkStatuses()
    {
        return $this->hasMany(OutOfFrameworkStatus::class, 'user_id');
    }

    public function unpaidLeaves()
    {
        return $this->hasMany(UnpaidLeave::class, 'user_id');
    }

    public function publicWorkHistories()
    {
        return $this->hasMany(PublicWorkHistory::class, 'user_id');
    }

    public function privateWorkHistories()
    {
        return $this->hasMany(PrivateWorkHistory::class, 'user_id');
    }

    public function userDecorations()
    {
        return $this->hasMany(UserDecoration::class, 'user_id');
    }

    public function disciplinaryActions()
    {
        return $this->hasMany(DisciplinaryAction::class, 'user_id');
    }

    public function educations()
    {
        return $this->hasMany(UserEducation::class, 'user_id');
    }

    public function languages()
    {
        return $this->hasMany(UserLanguage::class, 'user_id');
    }

    public function siblings()
    {
        return $this->hasMany(UserSibling::class, 'user_id');
    }

    public function children()
    {
        return $this->hasMany(UserChild::class, 'user_id');
    }

    /**
     * គណនារយៈពេលនៃការធ្វើការងាររបស់មន្ត្រី (អតីតភាពការងារ)
     */
    public function getServiceDurationAttribute(): ?array
    {
        $startDate = $this->first_service_date ?? $this->first_appointment_date;
        if (!$startDate) {
            return null;
        }

        try {
            $start = Carbon::parse($startDate);
            if (in_array($this->officer_status, ['RESIGNED', 'RETIRED', 'TRANSFERRED', 'SUSPENDED']) && $this->officer_status_date) {
                $end = Carbon::parse($this->officer_status_date);
            } else {
                $end = Carbon::now();
            }

            if ($end->lessThan($start)) {
                return ['years' => 0, 'months' => 0, 'days' => 0];
            }

            $diff = $start->diff($end);
            return [
                'years' => $diff->y,
                'months' => $diff->m,
                'days' => $diff->d,
                'total_days' => (int) $start->diffInDays($end),
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getServiceDurationFormattedAttribute(): ?string
    {
        $duration = $this->service_duration;
        if (!$duration) {
            return null;
        }

        $years = $duration['years'];
        $months = $duration['months'];
        $days = $duration['days'];

        $parts = [];
        if ($years > 0) {
            $parts[] = "{$years} ឆ្នាំ";
        }
        if ($months > 0) {
            $parts[] = "{$months} ខែ";
        }
        if ($years === 0 && $months === 0) {
            $parts[] = $days > 0 ? "{$days} ថ្ងៃ" : "ទើបចូលបម្រើការងារ";
        }

        return implode(' ', $parts);
    }
}