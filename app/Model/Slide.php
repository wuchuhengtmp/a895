<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    protected $table = 'slide';

    protected $fillable = [
        'url',
        'href',
        'order_num',
    ];
}
