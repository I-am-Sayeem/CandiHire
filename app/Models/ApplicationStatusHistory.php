<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationStatusHistory extends Model
{
    protected $table = 'application_status_histories';
    protected $primaryKey = 'StatusHistoryID';
    public $timestamps = false;

    protected $fillable = [
        'ApplicationID','Status','StatusDate','Notes','UpdatedBy'
    ];

    public function application() {
        return $this->belongsTo(JobApplication::class, 'ApplicationID');
    }
}
