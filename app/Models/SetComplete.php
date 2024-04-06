<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SetComplete extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'day_id', 'program_id', 'day_exercise_id', 'is_completed', 'set_id'];

    protected $table = 'set_completes';
}
