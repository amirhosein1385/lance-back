<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Profile extends Model
{
  protected $fillable = [
      'user_id',
      'profile_image',
      'description',
      'status',
      'location',
      'verified',
      'is_employer',
      'total_jobs_completed',
      'total_earnings',
      'response_time_hours',
      'completion_rate',
      'profile_views'
  ];

  protected $casts = [
      'status' => 'boolean',
      'verified' => 'boolean',
      'is_employer' => 'boolean',
      'completion_rate' => 'decimal:2'
  ];

  public function user(): BelongsTo
  {
      return $this->belongsTo(User::class);
  }

  public function skills(): BelongsToMany {
      return $this->belongsToMany(Skill::class)
          ->withPivot('proficiency_level', 'years_of_experience')
          ->withTimestamps();
  }


  public function courses(): HasMany
  {
      return $this->hasMany(Course::class);
  }

  public function works(): HasMany {
      return $this->hasMany(Work::class);
  }

  public function reviews():HasMany
  {
      return $this->hasMany(Review::class);

  }

  public function givenReviews(): HasMany
  {
     return $this->hasMany(Review::class , 'reviewer_id');
  }


}
