<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CvProcessingRecord extends Model
{
    protected $table = 'cv_processing_records';
    protected $primaryKey = 'RecordID';
    public $timestamps = true;

    protected $fillable = [
        'CandidateID','CvPath','ExtractedText','SkillsFound',
        'ExperienceYears','EducationLevel','Score'
    ];

    public function candidate() {
        return $this->belongsTo(Candidate::class, 'CandidateID');
    }
}
