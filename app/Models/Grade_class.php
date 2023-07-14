<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade_class extends Model
{
    use HasFactory;

    protected $table = 'grade_class';

    public $timestamps = true;
    protected $primaryKey = 'classId';
    protected $guarded = [];
    protected $casts = [
        
    ];
}
