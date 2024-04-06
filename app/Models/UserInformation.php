<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInformation extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'gender', 'height', 'weight', 'age', 'goal', 'focus', 'experience', 'equipment', 'interest'];

    protected $table = 'user_informations';

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
