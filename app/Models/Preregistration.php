<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Preregistration extends Model
{
    protected $table = "preregistrations";
    protected $fillable = ["phone_number"];
}
