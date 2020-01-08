<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
    protected $table = 'goods';

    protected $fillable = [
        'title',
        'total',
        'tags',
        'status',
        'content',
        'credit',
        'price',
        'thumb'
    ];
}
