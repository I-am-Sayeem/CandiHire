<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    protected $table = 'candidates';
    protected $primaryKey = 'CandidateID';
    public $timestamps = true;

    protected $fillable = [
        'FullName','Email','PhoneNumber','WorkType','Skills','Password',
        'IsActive','ProfilePicture','Location','Summary','LinkedIn','GitHub',
        'Portfolio','YearsOfExperience'
    ];

    public function cvs() { return $this->hasMany(CandidateCv::class, 'CandidateID'); }
    public function educations() { return $this->hasMany(CandidateEducation::class, 'CandidateID'); }
    public function experiences() { return $this->hasMany(CandidateExperience::class, 'CandidateID'); }
    public function projects() { return $this->hasMany(CandidateProject::class, 'CandidateID'); }
    public function skills() { return $this->hasOne(CandidateSkill::class, 'CandidateID'); }
}
