<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayComplete extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'day_id', 'program_id', 'is_completed'];
}
