<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Migrations extends Model
{
    use HasFactory;

    protected $table = 'migrations';

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $guarded = [];
    protected $casts = [
        
    ];
}
