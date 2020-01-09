<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CreditLog extends Model
{
    protected $table = 'credit_log';

    protected $fillable = [
        'title',
        'total',
        'status',
        'user_id'
    ];
}
