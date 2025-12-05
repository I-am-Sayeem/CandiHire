<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $table = 'complaints';
    protected $primaryKey = 'ComplaintID';
    public $timestamps = true;

    protected $fillable = [
        'UserID','UserType','Subject','Message','Status','AdminReply'
    ];
}
