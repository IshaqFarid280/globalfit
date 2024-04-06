<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    use HasFactory;

    protected $fillable = ['day_name', 'program_id', 'user_id'];

    public function dayCompleted(){
        return $this->hasOne(DayComplete::class, 'day_id', 'id');
    }
}
