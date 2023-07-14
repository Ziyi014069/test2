<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Books_units extends Model
{
    use HasFactory;

    protected $table = 'books_units';

    public $timestamps = true;
    protected $primaryKey = 'unitsId';
    protected $guarded = [];
    protected $casts = [
        
    ];
}
