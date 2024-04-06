<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = ['program_name', 'user_id'];

    public function assignPrograms()
    {
        return $this->hasMany(AssignProgram::class, 'program_id', 'id');
    }

    public function programCompleted(){
        return $this->hasOne(ProgramComplete::class, 'program_id', 'id');
    }
}
