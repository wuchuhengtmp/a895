<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GoodsComment extends Model
{
    protected $table = 'goods_comments';

    protected $fillable = [
        'content',
        'user_id',
        'goods_id',
        'rate',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function getImgAttribute($value)
    {
        return Storage::disk('img')->url($value);
    }
}
