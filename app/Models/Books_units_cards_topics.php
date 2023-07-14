<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Books_units_cards_topics extends Model
{
    use HasFactory;

    protected $table = 'books_units_cards_topics';

    public $timestamps = true;
    protected $primaryKey = 'topicsId';
    protected $guarded = [];
    protected $casts = [
        
    ];
}
