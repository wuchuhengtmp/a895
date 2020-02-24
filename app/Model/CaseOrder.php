<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CaseOrder extends Model
{
    protected $table = 'case_orders';

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
        'prepay_id'
    ];
    
}
