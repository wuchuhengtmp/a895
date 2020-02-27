<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
        $Order = DB::table('case_orders')->where('id', $this->order_id)->first();
        
        if ($Order->app_pay_type === 'installment') {
            // 判定未支付和支付失败是否逾期
            if (in_array($value, [104]) && strtotime($this->pay_at) < time()) {
                $this->status = 103;
                $this->save(); //:xxx 这里后$this->status 会被重置,所以只能 返回103而不是$this->status
                return 103;
            }
        }
        return $value;
    }

    public function getTotalPriceAttribute($value)
    {
    }

    public function caseOrder()
    {
        return $this->hasOne(CaseOrder::class, 'id', 'order_id');
    }

    public function getImagesAttribute($value)
    {
        if ($value) {
            $images = json_decode($value);
            array_walk($images, function(&$el) {
                $el = Storage::disk('img')->url($el);
            });
            return $images;
        }
    }
}
