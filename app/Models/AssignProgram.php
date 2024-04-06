<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignProgram extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'program_id'];

    public function program(){
        return $this->belongsTo(Program::class, 'program_id', 'id');
    }
}
