<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    protected $table = 'orders';

    use SoftDeletes;
    
    protected $fillable = [
        'out_trade_no',
        'user_id',
        'goods_id',
        'num',
        'pay_type',
        'address_info',
        'pay_at',
        'status',
        'price',
        'credit',
        'alipay_trade_no',
        'title',
        'goods_info',
        'express_co'
    ];

    protected $appends = [
        'reciever',
    ];

    /**
    * 收货人
    *
    */
    public function getRecieverAttribute()
    {
        $User = json_decode($this->address_info);
        return $User->name;
    }

    public function goods()
    {
        return $this->hasOne(Goods::class, 'id', 'goods_id');
    }
}
