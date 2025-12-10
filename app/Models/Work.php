<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Work extends Model
{
    protected $fillable = [
        'profile_id',
        'title',
        'description',
        'image_url',
        'project_url',
        'completed_at',
        'client_name',
        'order',
        'is_featured'
    ];

    protected $casts = [
        'completed_at' => 'date',
        'is_featured' => 'boolean'
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class);
    }
}
