<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Tmp extends Model
{
    protected $table = 'tmp';

    protected $fillable = [
        'id',
        'name',
        'code',
        'pinYin' // <= 
    ];

    protected $appends = [
        /* 'test' // <= */
    ];

    public function setTestAttribute($value)
    {
        $this->pinYin = $value;
    }
}
