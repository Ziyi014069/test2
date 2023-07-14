<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin_log extends Model
{
    use HasFactory;

    protected $table = 'admin_log';

    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $guarded = [];
    protected $casts = [
        
    ];
}
