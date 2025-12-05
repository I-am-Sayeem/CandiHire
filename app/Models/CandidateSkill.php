<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidateSkill extends Model
{
    protected $table = 'candidate_skills';
    protected $primaryKey = 'SkillID';
    public $timestamps = true;

    protected $fillable = [
        'CandidateID','ProgrammingLanguages','Frameworks','Databases',
        'Tools','SoftSkills','Languages','Certifications'
    ];

    public function candidate() {
        return $this->belongsTo(Candidate::class, 'CandidateID');
    }
}
