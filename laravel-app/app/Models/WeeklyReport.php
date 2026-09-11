<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class WeeklyReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'weekly_reports';

    protected $fillable = [
        'user_id',
        'department_id',
        'office_id',
        'position_id',
        'year',
        'month',
        'week_number',
        'start_date',
        'end_date',
        'title',
        'completed_tasks',
        'planned_tasks',
        'challenges',
        'attachment_path',
        'attachment_name',
        'status',
        'submitted_at',
        'supervisor_remarks',
        'reviewed_by',
        'reviewed_at',
        'leadership_remarks',
        'leadership_reviewed_by',
        'leadership_reviewed_at',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'week_number' => 'integer',
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'leadership_reviewed_at' => 'datetime',
    ];

    protected $appends = [
        'total_tasks_count',
        'pending_tasks_count',
        'in_progress_tasks_count',
        'completed_tasks_count',
        'completion_rate',
        'leadership_reviewer',
    ];

    /**
     * បញ្ជីកិច្ចការងារជាក់ស្តែង
     */
    public function tasks()
    {
        return $this->hasMany(WeeklyReportTask::class, 'weekly_report_id');
    }

    public function getTotalTasksCountAttribute()
    {
        return $this->relationLoaded('tasks') ? $this->tasks->count() : $this->tasks()->count();
    }

    public function getPendingTasksCountAttribute()
    {
        return $this->relationLoaded('tasks')
            ? $this->tasks->where('status', 'PENDING')->count()
            : $this->tasks()->where('status', 'PENDING')->count();
    }

    public function getInProgressTasksCountAttribute()
    {
        return $this->relationLoaded('tasks')
            ? $this->tasks->where('status', 'IN_PROGRESS')->count()
            : $this->tasks()->where('status', 'IN_PROGRESS')->count();
    }

    public function getCompletedTasksCountAttribute()
    {
        return $this->relationLoaded('tasks')
            ? $this->tasks->where('status', 'COMPLETED')->count()
            : $this->tasks()->where('status', 'COMPLETED')->count();
    }

    public function getCompletionRateAttribute()
    {
        $total = $this->total_tasks_count;
        if ($total === 0) return 0;
        $completed = $this->completed_tasks_count;
        return round(($completed / $total) * 100);
    }

    /**
     * ម្ចាស់របាយការណ៍ (មន្ត្រីដែលកត់ត្រា)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * នាយកដ្ឋាន
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * ការិយាល័យ
     */
    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    /**
     * តួនាទី
     */
    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    /**
     * ថ្នាក់ដឹកនាំដែលបានពិនិត្យផ្ទាល់
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * ថ្នាក់ដឹកនាំអគ្គនាយកដ្ឋានដែលបានចារបន្ថែមពីលើ
     */
    public function leadershipReviewer()
    {
        return $this->belongsTo(User::class, 'leadership_reviewed_by');
    }

    /**
     * Accessor សម្រាប់ leadership_reviewer (គាំទ្រទាំង relations និង JSON serializing)
     */
    public function getLeadershipReviewerAttribute()
    {
        if ($this->relationLoaded('leadershipReviewer')) {
            return $this->getRelation('leadershipReviewer');
        }
        return $this->leadership_reviewed_by ? User::with('position')->find($this->leadership_reviewed_by) : null;
    }

    /**
     * Scope Filter តាមសិទ្ធិមើលរបស់មន្ត្រី ឬថ្នាក់ដឹកនាំ (Hierarchy-based visibility)
     */
    public function scopeVisibleTo(Builder $query, User $viewer)
    {
        // 1. ប្រសិនបើជា Admin ប្រព័ន្ធ អាចមើលរបាយការណ៍ទាំងអស់ (តែ Draft របស់អ្នកដទៃមិនរាប់បញ្ចូល)
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');

        // កម្រិតតួនាទីរបស់អ្នកមើល (Position Level, 1 = អគ្គនាយក, 11 = អ្នកបើកបរ)
        $viewerPosLevel = $viewer->position ? (int)$viewer->position->level : 99;
        $viewerDeptId = $viewer->department_id;
        $viewerOfficeId = $viewer->office_id;

        return $query->where(function ($q) use ($viewer, $isAdmin, $viewerPosLevel, $viewerDeptId, $viewerOfficeId) {
            // ក. របាយការណ៍ផ្ទាល់ខ្លួន អាចមើលឃើញគ្រប់ស្ថានភាព (Draft, Submitted, Reviewed)
            $q->where('weekly_reports.user_id', $viewer->id);

            // ខ. របាយការណ៍របស់អ្នកដទៃ ត្រូវតែស្ថិតក្នុងស្ថានភាព SUBMITTED ឬ REVIEWED
            $q->orWhere(function ($subQ) use ($viewer, $isAdmin, $viewerPosLevel, $viewerDeptId, $viewerOfficeId) {
                $subQ->where('weekly_reports.user_id', '!=', $viewer->id)
                     ->whereIn('weekly_reports.status', ['SUBMITTED', 'REVIEWED']);

                // ប្រសិនបើជា ADMIN មើលបានទាំងអស់ដែលបាន Submit
                if ($isAdmin) {
                    return;
                }

                // ប្រសិនបើគ្មានតួនាទី ឬជាមន្ត្រីកម្រិតទាប (Level >= 9) មិនអាចមើលរបាយការណ៍របស់អ្នកដទៃបានទេ
                if ($viewerPosLevel >= 9) {
                    $subQ->whereRaw('1 = 0'); // Empty
                    return;
                }

                // ភ្ជាប់ជាមួយតារាង positions របស់ម្ចាស់របាយការណ៍ ដើម្បីប្រៀបធៀប level
                $subQ->whereHas('position', function ($posQ) use ($viewerPosLevel) {
                    // តួនាទីអ្នកមើលត្រូវតែខ្ពស់ជាងម្ចាស់របាយការណ៍ (level លេខតូចជាង គឺឋានៈខ្ពស់ជាង)
                    $posQ->where('level', '>', $viewerPosLevel);
                });

                // កំណត់តាមរង្វង់ស្ថាប័ន (Chain of Command):
                if ($viewerPosLevel <= 2 || is_null($viewerDeptId)) {
                    // ១. ថ្នាក់ដឹកនាំអគ្គនាយកដ្ឋាន (អគ្គនាយក Level 1, អគ្គនាយករង Level 2)
                    // មើលបានគ្រប់នាយកដ្ឋាន និងគ្រប់ការិយាល័យ ឱ្យតែឋានៈតូចជាងខ្លួន
                    return;
                } elseif ($viewerPosLevel <= 5 || is_null($viewerOfficeId)) {
                    // ២. ថ្នាក់ដឹកនាំនាយកដ្ឋាន (ប្រធាននាយកដ្ឋាន Level 3/4, អនុប្រធាននាយកដ្ឋាន Level 5)
                    // មើលបានតែមន្ត្រីក្នុងនាយកដ្ឋានរបស់ខ្លួន
                    $subQ->where('weekly_reports.department_id', $viewerDeptId);
                } elseif ($viewerPosLevel <= 8 && !is_null($viewerOfficeId)) {
                    // ៣. ថ្នាក់ដឹកនាំការិយាល័យ (ប្រធានការិយាល័យ Level 6/7, អនុប្រធានការិយាល័យ Level 8)
                    // មើលបានតែមន្ត្រីក្នុងនាយកដ្ឋាន និងការិយាល័យរបស់ខ្លួន
                    $subQ->where('weekly_reports.department_id', $viewerDeptId)
                         ->where('weekly_reports.office_id', $viewerOfficeId);
                } else {
                    $subQ->whereRaw('1 = 0');
                }
            });
        });
    }

    /**
     * ពិនិត្យថាតើ User ណាម្នាក់មានសិទ្ធិមើលរបាយការណ៍នេះដែរឬទេ
     */
    public function canBeViewedBy(User $viewer): bool
    {
        // ម្ចាស់ផ្ទាល់
        if ($this->user_id === $viewer->id) {
            return true;
        }

        // របាយការណ៍ DRAFT មើលឃើញតែម្ចាស់ផ្ទាល់
        if ($this->status === 'DRAFT') {
            return false;
        }

        // Admin ប្រព័ន្ធ
        if (strtoupper($viewer->level ?? '') === 'ADMIN') {
            return true;
        }

        $viewerPosLevel = $viewer->position ? (int)$viewer->position->level : 99;
        $authorPosLevel = $this->position ? (int)$this->position->level : ($this->user?->position?->level ?? 99);

        // ឋានៈអ្នកមើលត្រូវតែខ្ពស់ជាងម្ចាស់របាយការណ៍
        if ($viewerPosLevel >= $authorPosLevel) {
            return false;
        }

        // ថ្នាក់ដឹកនាំអគ្គនាយកដ្ឋាន
        if ($viewerPosLevel <= 2 || is_null($viewer->department_id)) {
            return true;
        }

        // ថ្នាក់ដឹកនាំនាយកដ្ឋាន
        if ($viewerPosLevel <= 5 || is_null($viewer->office_id)) {
            return $this->department_id == $viewer->department_id;
        }

        // ថ្នាក់ដឹកនាំការិយាល័យ
        if ($viewerPosLevel <= 8 && !is_null($viewer->office_id)) {
            return ($this->department_id == $viewer->department_id) && ($this->office_id == $viewer->office_id);
        }

        return false;
    }

    /**
     * ពិនិត្យថាតើ User អាចកែសម្រួលរបាយការណ៍បានទេ
     */
    public function canBeEditedBy(User $user): bool
    {
        if (strtoupper($user->level ?? '') === 'ADMIN') {
            return true;
        }

        // មានតែម្ចាស់របាយការណ៍ទើបអាចកែប្រែបាន
        if ($this->user_id !== $user->id) {
            return false;
        }

        // ប្រសិនបើត្រូវបានពិនិត្យរួចរាល់ហើយ មិនអនុញ្ញាតឱ្យកែប្រែដោយសេរីឡើយ
        return $this->status !== 'REVIEWED';
    }

    /**
     * ពិនិត្យថាតើ User អាចដាក់ចំណារ ឬ Review បានទេ (ទាំងថ្នាក់ដឹកនាំផ្ទាល់ ឬចារបន្ថែមពីលើ)
     */
    public function canBeReviewedBy(User $reviewer): bool
    {
        return $this->canReviewAsSupervisor($reviewer) || $this->canAddLeadershipRemark($reviewer);
    }

    /**
     * ពិនិត្យថាតើ User អាចដាក់ចំណារជាថ្នាក់ដឹកនាំផ្ទាល់ (Direct Supervisor Remark)
     * - មន្ត្រី ឬទាបជាងមន្ត្រី (Level >= 9): ប្រធានការិយាល័យ (Level 6-7) ជាអ្នកចារ
     * - អនុប្រធានការិយាល័យ (Level 8): ប្រធានការិយាល័យ (Level 6-7) ជាអ្នកចារ
     * - ប្រធានការិយាល័យ (Level 6-7) & អនុប្រធាននាយកដ្ឋាន (Level 5): ប្រធាននាយកដ្ឋាន (Level 3-4) ជាអ្នកចារ
     * - អនុប្រធានការិយាល័យ (Level 8): គ្មានសិទ្ធិចារឡើយ (បានត្រឹមតែមើល)
     * - អនុប្រធាននាយកដ្ឋាន (Level 5): គ្មានសិទ្ធិចារឡើយ (មានតែប្រធាននាយកដ្ឋានទើបចារបាន)
     */
    public function canReviewAsSupervisor(User $reviewer): bool
    {
        if ($this->user_id === $reviewer->id || $this->status === 'DRAFT') {
            return false;
        }

        if (strtoupper($reviewer->level ?? '') === 'ADMIN') {
            return true;
        }

        $revPosLevel = $reviewer->position ? (int)$reviewer->position->level : 99;
        $authorPosLevel = $this->position ? (int)$this->position->level : ($this->user?->position?->level ?? 99);

        // ១. ប្រសិនបើជាអនុប្រធានការិយាល័យ (Level 8) ឬមន្ត្រីទូទៅ (Level >= 9):
        // គ្មានសិទ្ធិចារដាច់ខាត (បានត្រឹមតែមើល)
        if ($revPosLevel >= 8) {
            return false;
        }

        // ២. ប្រសិនបើជាអនុប្រធាននាយកដ្ឋាន (Level 5):
        // គ្មានសិទ្ធិចារលើប្រធានការិយាល័យ ឬមន្ត្រីឡើយ (មានតែប្រធាននាយកដ្ឋានទើបចារបាន)
        if ($revPosLevel == 5) {
            return false;
        }

        // ៣. ប្រធានការិយាល័យ / ប្រធានស្តីទី (Level 6-7):
        // សរសេរចំណារលើមន្ត្រី (Level >= 9) និងអនុប្រធានការិយាល័យ (Level 8) ក្នុងបន្ទប់ការិយាល័យរបស់ខ្លួន
        if ($revPosLevel >= 6 && $revPosLevel <= 7) {
            return ($authorPosLevel >= 8) &&
                   ($this->department_id == $reviewer->department_id) &&
                   ($this->office_id == $reviewer->office_id);
        }

        // ៤. ប្រធាននាយកដ្ឋាន / ប្រធានស្តីទី (Level 3-4):
        // សរសេរចំណារលើប្រធានការិយាល័យ (Level 6-7), អនុប្រធាននាយកដ្ឋាន (Level 5),
        // ព្រមទាំងមន្ត្រីក្រោមឱវាទក្នុងនាយកដ្ឋានរបស់ខ្លួន
        if ($revPosLevel >= 3 && $revPosLevel <= 4) {
            return ($authorPosLevel > $revPosLevel) &&
                   ($this->department_id == $reviewer->department_id);
        }

        // ៥. អគ្គនាយក (Level 1) និង អគ្គនាយករង (Level 2):
        // អាចបំពេញចំណារផ្ទាល់បាន ប្រសិនបើសិនជាចង់បំពេញលើរបាយការណ៍ដែលពុំទាន់មានចំណារ
        if ($revPosLevel <= 2) {
            return true;
        }

        return false;
    }

    /**
     * ពិនិត្យថាតើ User អាចចារបន្ថែមពីលើ (Leadership Additional Remarks)
     * - ផ្តល់ជូនសម្រាប់តែ អគ្គនាយក (Level 1), អគ្គនាយករង (Level 2), និង Admin
     */
    public function canAddLeadershipRemark(User $reviewer): bool
    {
        if ($this->user_id === $reviewer->id || $this->status === 'DRAFT') {
            return false;
        }

        if (strtoupper($reviewer->level ?? '') === 'ADMIN') {
            return true;
        }

        $revPosLevel = $reviewer->position ? (int)$reviewer->position->level : 99;
        return ($revPosLevel <= 2);
    }
}
