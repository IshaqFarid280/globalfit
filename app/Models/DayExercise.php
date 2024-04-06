<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayExercise extends Model
{
    use HasFactory;

    protected $fillable = ['day_id', 'exercise_name', 'exercise_image', 'exercise_description', 'user_id'];

    protected $table = 'day_exercises';

    public function dayExerciseCompleted(){
        return $this->hasOne(DayExerciseComplete::class, 'day_exercise_id', 'id');
    }
}
