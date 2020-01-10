<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Designer extends Model
{
    protected $table ='designer';

    protected $fillable = [
        'name',
        'longitude',
        'latitude',
        'avatar',
    ];
}
