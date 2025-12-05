<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidateExperience extends Model
{
    protected $table = 'candidate_experiences';
    protected $primaryKey = 'ExperienceID';
    public $timestamps = true;

    protected $fillable = [
        'CandidateID','JobTitle','Company','StartDate','EndDate',
        'Description','Location'
    ];

    public function candidate() {
        return $this->belongsTo(Candidate::class, 'CandidateID');
    }
}
