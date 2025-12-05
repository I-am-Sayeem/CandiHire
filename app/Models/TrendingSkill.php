<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrendingSkill extends Model
{
    protected $table = 'trending_skills';
    protected $primaryKey = 'SkillID';
    public $timestamps = true;

    protected $fillable = [
        'SkillName','Category','Popularity','Source'
    ];
}
