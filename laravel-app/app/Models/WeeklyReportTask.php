<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeeklyReportTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'weekly_report_tasks';

    protected $fillable = [
        'weekly_report_id',
        'user_id',
        'department_id',
        'office_id',
        'task_name',
        'description',
        'status',
        'priority',
        'progress_percent',
        'result_notes',
        'due_date',
        'completed_at',
    ];

    protected $casts = [
        'progress_percent' => 'integer',
        'due_date' => 'date:Y-m-d',
        'completed_at' => 'datetime',
    ];

    public function weeklyReport()
    {
        return $this->belongsTo(WeeklyReport::class, 'weekly_report_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }
}
