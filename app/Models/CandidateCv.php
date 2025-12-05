<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidateCv extends Model
{
    protected $table = 'candidate_cvs';
    protected $primaryKey = 'CvID';
    public $timestamps = true;

    protected $fillable = [
        'CandidateID','FirstName','LastName','Email','Phone','Address','Summary'
    ];

    public function candidate() {
        return $this->belongsTo(Candidate::class, 'CandidateID');
    }
}
