<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiMatchingResult extends Model
{
    protected $table = 'ai_matching_results';
    protected $primaryKey = 'MatchID';
    public $timestamps = true;

    protected $fillable = [
        'CandidateID','JobID','CompanyID','MatchPercentage',
        'SkillsMatch','ExperienceMatch','EducationMatch','LocationMatch',
        'SalaryMatch','MatchFactors'
    ];

    public function candidate() {
        return $this->belongsTo(Candidate::class, 'CandidateID');
    }

    public function job() {
        return $this->belongsTo(JobPosting::class, 'JobID');
    }

    public function company() {
        return $this->belongsTo(Company::class, 'CompanyID');
    }
}
