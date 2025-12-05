<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'NotificationID';
    public $timestamps = true;

    protected $fillable = [
        'UserID','UserType','Title','Message','IsRead','Link','Category'
    ];
}
