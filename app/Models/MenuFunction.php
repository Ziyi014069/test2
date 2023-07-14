<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuFunction extends Model
{
    use HasFactory;

    protected $table = 'menu_function';

    protected $primaryKey = 'menuFunctionId';
    protected $guarded = [];
    public $timestamps = true;
    protected $casts = [

        'menuFunctionOfParentId' => 'integer',
        'isCategory' => 'integer',
        'isOperation' => 'integer',
        'isFunction' => 'integer',
        'isHiddenTag' => 'integer',
        'isShowLink' => 'integer',
        'isChildren' => 'integer',
        'orderOfMenuFunction' => 'integer',
        'isEnabled' => 'integer',

        'isRemoved' => 'integer',

    ];
}
