<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Books_teacher_mapping extends Model
{
    use HasFactory;

    protected $table = 'books_teacher_mapping';

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $guarded = [];
    protected $casts = [
        
    ];
}
