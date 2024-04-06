<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramWorkout extends Model
{
    use HasFactory;

    protected $fillable = ['program_id', 'day_id', 'exercise_id', 'set_id'];

    public function programs(){
        return $this->belongsTo(Program::class, 'program_id', 'id');
    }

    public function days(){
        return $this->belongsTo(Day::class, 'day_id', 'id');
    }
}
