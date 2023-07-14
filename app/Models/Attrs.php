<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attrs extends Model
{
    use HasFactory;

    protected $table = 'attrs';

    public $timestamps = true;
    protected $primaryKey = 'attrsId';
    protected $guarded = [];
    protected $casts = [
        
    ];
}
