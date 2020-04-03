<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseOrder extends Model
{
    use SoftDeletes;

    protected $table = 'case_orders';

    protected $appends = [
        'title',
        'pay_time_detail'
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
        'times',
        'app_pay_type',
        'reply',
        'compact_url',
        'refund_content',
        'refund_image',
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

    /**
     * 项目分期情况
     */
    public function getPayTimeDetailAttribute()
    {
        if ($this->app_pay_type === 'total') {
            return '全款支付';
        } else {
            $count = $this->payTimes->count();
            $bePay = 0;
            $paing = 0;
            $will_be_pay = 0;
            $over_pay = 0;
            foreach($this->payTimes as $PayTime) {
                switch($PayTime->status) {
                    case 100:
                        $bePay++;
                        break;
                    case 101:
                        $paing++;
                        break;
                    case 102:
                        $will_be_pay++;
                        break;
                    case 103:
                        $over_pay++;
                        break;
                    case 104:
                        $will_be_pay++;
                        break;
                }
            }
            return "{$count}/{$bePay}/{$paing}/{$will_be_pay}/{$over_pay}";
        }
    } 

    public function payTimes()
    {
        return $this->hasMany(PayTimes::class, 'order_id', 'id');
    }

    /**
     * 订单状态
     *
     */
    public function getStatusAttribute($status)
    { 
            return $status; 
    }

    public  function  comment()
    {
        return $this->hasOne(CaseOrderComment::class, 'order_id', 'id');
    }
}

