<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $table = 'teacher';

    public $timestamps = true;
    protected $primaryKey = 'teacherId';
    protected $guarded = [];
    protected $casts = [
        
    ];
}
