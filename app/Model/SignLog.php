<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SignLog extends Model
{
    protected $table = 'sign_log';

    protected $fillable = [
        'user_id'
    ];
}
