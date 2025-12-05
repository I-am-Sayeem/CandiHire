<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    protected $table = 'exam_attempts';
    protected $primaryKey = 'AttemptID';
    public $timestamps = true;

    protected $fillable = [
        'ScheduleID','CandidateID','ExamID','StartTime','EndTime',
        'Status','Score','TotalQuestions','CorrectAnswers','TimeSpent'
    ];

    public function schedule() {
        return $this->belongsTo(ExamSchedule::class, 'ScheduleID');
    }

    public function candidate() {
        return $this->belongsTo(Candidate::class, 'CandidateID');
    }

    public function exam() {
        return $this->belongsTo(Exam::class, 'ExamID');
    }

    public function answers() {
        return $this->hasMany(ExamAnswer::class, 'AttemptID');
    }
}
