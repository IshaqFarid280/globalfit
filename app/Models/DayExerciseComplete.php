<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayExerciseComplete extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'program_id', 'day_id', 'is_completed', 'day_exercise_id'];

    protected $table = 'day_exercise_completes';
}
