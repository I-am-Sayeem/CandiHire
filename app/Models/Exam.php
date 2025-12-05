<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $table = 'exams';
    protected $primaryKey = 'ExamID';
    public $timestamps = true;

    protected $fillable = [
        'CompanyID','ExamTitle','ExamType','Description','Instructions',
        'Duration','QuestionCount','PassingScore','MaxAttempts','IsActive',
        'CreatedBy'
    ];

    public function company() {
        return $this->belongsTo(Company::class, 'CompanyID');
    }

    public function questions() {
        return $this->hasMany(ExamQuestion::class, 'ExamID');
    }

    public function schedules() {
        return $this->hasMany(ExamSchedule::class, 'ExamID');
    }

    public function attempts() {
        return $this->hasMany(ExamAttempt::class, 'ExamID');
    }
}
