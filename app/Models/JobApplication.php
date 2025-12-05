<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $table = 'job_applications';
    protected $primaryKey = 'ApplicationID';
    public $timestamps = true;

    protected $fillable = [
        'CandidateID','JobID','ApplicationDate','Status','CoverLetter',
        'ResumePath','Notes','ContactPerson','ContactEmail','SalaryExpectation',
        'AvailabilityDate'
    ];

    public function candidate() {
        return $this->belongsTo(Candidate::class, 'CandidateID');
    }

    public function job() {
        return $this->belongsTo(JobPosting::class, 'JobID');
    }

    public function statusHistory() {
        return $this->hasMany(ApplicationStatusHistory::class, 'ApplicationID');
    }

    public function interview() {
        return $this->hasOne(Interview::class, 'JobID', 'JobID')
                    ->whereColumn('CandidateID', 'CandidateID');
    }
}
