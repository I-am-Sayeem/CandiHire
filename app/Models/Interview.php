<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    protected $table = 'interviews';
    protected $primaryKey = 'InterviewID';
    public $timestamps = true;

    protected $fillable = [
        'CandidateID','CompanyID','JobID',
        'InterviewDate','InterviewTime','InterviewMode',
        'Status','Notes','MeetingLink','Location'
    ];

    public function candidate() {
        return $this->belongsTo(Candidate::class, 'CandidateID');
    }

    public function company() {
        return $this->belongsTo(Company::class, 'CompanyID');
    }

    public function job() {
        return $this->belongsTo(JobPosting::class, 'JobID');
    }
}
