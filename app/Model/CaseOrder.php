<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CaseOrder extends Model
{
    protected $table = 'case_orders';

    protected $appends = [
        'title'
    ];

    protected $fillable = [
        'user_id',
        'case_id',
        'case_info',
        'prepay_price',
        'area',
        'room',
        'city_code',
        'phone',
        'name',
        'status',
        'pay_type',
        'out_trade_no',
        'prepay_id',
        'balance',
        'stage'
    ];

    public function city()
    {
        return $this->hasOne(ChinaArea::class, 'code', 'city_code');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * 项目title
     */
    public function getTitleAttribute()
    {
        $title = json_decode($this->case_info, true)['title'];
        return $title;
    } 
}

