<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = ['name', 'slug', 'category'];

    public function profiles()
    {
        return $this->belongsToMany(Profile::class)
            ->withPivot('proficiency_level', 'years_of_experience')
            ->withTimestamps();
    }

    public function works()
    {
        return $this->belongsToMany(Work::class);
    }
}
