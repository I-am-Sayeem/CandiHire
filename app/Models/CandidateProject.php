<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidateProject extends Model
{
    protected $table = 'candidate_projects';
    protected $primaryKey = 'ProjectID';
    public $timestamps = true;

    protected $fillable = [
        'CandidateID','ProjectName','Role','StartDate','EndDate',
        'Description','Technologies','ProjectUrl'
    ];

    public function candidate() {
        return $this->belongsTo(Candidate::class, 'CandidateID');
    }
}
