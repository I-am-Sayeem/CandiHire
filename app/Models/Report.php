<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'reports';
    protected $primaryKey = 'ReportID';
    public $timestamps = true;

    protected $fillable = [
        'ReportType','GeneratedBy','Description','FilePath'
    ];
}
