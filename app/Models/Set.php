<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Set extends Model
{
    use HasFactory;

    protected $fillable = ['kg', 'reps', 'day_exercise_id', 'user_id'];

    public function programWorkouts(){
        return $this->hasMany(Set::class, 'set_id', 'id');
    }

    public function dayExercise(){
        return $this->belongsTo(DayExercise::class, 'day_exercise_id', 'id');
    }

    public function setCompleted(){
        return $this->hasOne(SetComplete::class, 'set_id', 'id');
    }
}

