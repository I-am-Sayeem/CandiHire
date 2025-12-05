<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $table = 'system_settings';
    protected $primaryKey = 'SettingID';
    public $timestamps = true;

    protected $fillable = [
        'SettingKey','SettingValue','Description','Category'
    ];
}
