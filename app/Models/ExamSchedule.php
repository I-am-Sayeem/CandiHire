<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSchedule extends Model
{
    protected $table = 'exam_schedules';
    protected $primaryKey = 'ScheduleID';
    public $timestamps = true;

    protected $fillable = [
        'ExamID','CandidateID','JobID','ScheduledDate','ScheduledTime',
        'Status','Duration','AttemptsUsed','MaxAttempts'
    ];

    public function exam() {
        return $this->belongsTo(Exam::class, 'ExamID');
    }

    public function candidate() {
        return $this->belongsTo(Candidate::class, 'CandidateID');
    }

    public function job() {
        return $this->belongsTo(JobPosting::class, 'JobID');
    }

    public function attempts() {
        return $this->hasMany(ExamAttempt::class, 'ScheduleID');
    }
}
