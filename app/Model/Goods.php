<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
    protected $table = 'goods';

    protected $fillable = [
        'out_trade_no',
        'user_id',
        'goods_id',
        'total',
        'pay_type',
        'address_info',
        'pay_at',
        'status',
        'total_price',
        'total_credit',
        'alipay_trade_no',
        'express_no',
        'title',
        'created_at',
        'prepay_id',
        'app_pay_sign'
    ];

    public function comments()
    {
        return $this->hasMany(GoodsComment::class, 'goods_id', 'id');
    }
}
