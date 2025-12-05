<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidateEducation extends Model
{
    protected $table = 'candidate_educations';
    protected $primaryKey = 'EducationID';
    public $timestamps = true;

    protected $fillable = [
        'CandidateID','Degree','Institution','StartYear','EndYear',
        'GPA','Location','Coursework'
    ];

    public function candidate() {
        return $this->belongsTo(Candidate::class, 'CandidateID');
    }
}
