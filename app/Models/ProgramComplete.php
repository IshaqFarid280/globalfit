<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramComplete extends Model
{
    use HasFactory;

    protected $fillable = ['program_id', 'user_id', 'is_completed'];
    protected $table = 'program_completes';

    public function program(){
        return $this->belongsTo(Program::class, 'program_id', 'id');
    }
}
