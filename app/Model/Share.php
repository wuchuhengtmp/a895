<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Share extends Model
{
    protected $table = 'shares';

    protected $fullable = [
        'user_id',
        'user_id'
    ];

    public function shareUser()
    {
        return $this->hasOne(User::class, 'id', 'share_id');
    }
}
