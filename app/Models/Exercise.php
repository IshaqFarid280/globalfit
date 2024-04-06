<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    use HasFactory;

    protected $table = 'exercises';

    protected $fillable = ['category_id', 'exercise_name', 'exercise_description', 'exercise_gif'];

    public function category(){
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}
