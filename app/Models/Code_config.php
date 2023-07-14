<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Code_config extends Model
{
    use HasFactory;

    protected $table = 'code_config';

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $guarded = [];
    protected $casts = [
        
    ];
}
