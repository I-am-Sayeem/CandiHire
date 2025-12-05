<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    protected $table = 'job_postings';
    protected $primaryKey = 'JobID';
    public $timestamps = true;

    protected $fillable = [
        'CompanyID','JobTitle','Department','JobDescription','Requirements',
        'Responsibilities','Skills','Location','JobType','SalaryMin','SalaryMax',
        'Currency','ExperienceLevel','EducationLevel','Status','PostedDate',
        'ClosingDate','ApplicationCount'
    ];

    public function company() {
        return $this->belongsTo(Company::class, 'CompanyID');
    }

    public function applications() {
        return $this->hasMany(JobApplication::class, 'JobID');
    }

    public function examSchedules() {
        return $this->hasMany(ExamSchedule::class, 'JobID');
    }

    public function interviews() {
        return $this->hasMany(Interview::class, 'JobID');
    }

    public function aiMatches() {
        return $this->hasMany(AiMatchingResult::class, 'JobID');
    }
}
