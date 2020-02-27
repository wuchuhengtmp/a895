<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PayTimes extends Model
{
    protected $table = 'pay_times';

    protected $fillable = [
        'total_price',
        'order_id',
        'status',
        'pay_at',
        'reply',
        'images'
    ];

    public function getStatusAttribute($value)
    {
        // 判定未支付和支付失败是否逾期
        if (in_array($value, [102, 104]) && strtotime($this->pay_at) < time()) {
            $this->status = 103;
            $this->save(); //:xxx 这里后$this->status 会被重置,所以只能 返回103而不是$this->status
            return 103;
        }
        return $value;
    }

    public function getTotalPriceAttribute($value)
    {
        return number_format(round($value, 2), 2);
    }

    public function caseOrder()
    {
        return $this->hasOne(CaseOrder::class, 'id', 'order_id');
    }
}
