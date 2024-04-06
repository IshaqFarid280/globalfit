<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EditSet extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'kg', 'reps', 'day_exercise_id', 'set_id'];

    protected $table = 'edited_sets';
}
