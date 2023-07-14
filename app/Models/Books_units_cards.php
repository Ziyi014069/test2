<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Books_units_cards extends Model
{
    use HasFactory;

    protected $table = 'books_units_cards';

    public $timestamps = true;
    protected $primaryKey = 'cardsId';
    protected $guarded = [];
    protected $casts = [
        
    ];
}
