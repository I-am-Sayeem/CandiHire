<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';
    protected $primaryKey = 'CompanyID';
    public $timestamps = true;

    protected $fillable = [
        'CompanyName','Industry','CompanySize','Email','PhoneNumber',
        'CompanyDescription','Description','Password','IsActive','Website','Logo',
        'Address','City','State','Country','PostalCode'
    ];

    public function jobPostings() {
        return $this->hasMany(JobPosting::class, 'CompanyID');
    }

    public function interviews() {
        return $this->hasMany(Interview::class, 'CompanyID');
    }

    public function aiMatches() {
        return $this->hasMany(AiMatchingResult::class, 'CompanyID');
    }
}
