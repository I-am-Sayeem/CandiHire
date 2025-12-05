<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CvProcessingLog extends Model
{
    protected $table = 'cv_processing_logs';
    protected $primaryKey = 'LogID';
    public $timestamps = true;

    protected $fillable = [
        'RecordID','Action','Details','Timestamp'
    ];
}
