<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'profile_id',
        'title',
        'institution',
        'description',
        'completion_date',
        'certificate_url',
        'is_verified',
        'price',
        'discounted_price',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'is_verified' => 'boolean'
    ];

    public function students() {
        return $this->belongsToMany(Student::class, 'course_student', 'course_id', 'student_id');
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
