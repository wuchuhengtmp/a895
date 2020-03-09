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
        'compact_url'
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
        // 分期申请成功阶段尝试推向支付阶段
        $is_next_step = false;
        if ($this->app_pay_type === 'installment' && $status  == 200) {
            foreach($this->payTimes as $PayTime) {
                if ($PayTime->status !== 104)  {
                    $is_next_step = true;
                    break;
                }
            }
        }
        // 分期支付阶段
        if ($this->app_pay_type == 'installment' && (in_array($status, [300, 301, 302, 303]) || $is_next_step)) {
            $status = [
                'be_pay'   => 0,
                'paying'   => 0,
                'fail_pay' => 0,
                'over_pay' => 0,
                'no_pay'   => 0,
            ];
            foreach($this->payTimes as $payTime) {
                switch($payTime->status) {
                case 100:
                    $status['be_pay']   = 300;
                    break;
                case 101:
                    $status['paying']   = 301;
                    break;
                case 102:
                    $status['fail_pay'] = 302;
                    break;
                case 103:
                    $status['over_pay'] = 303;
                    break;
                case 104:
                    $status['no_pay']   = 301;
                    break;
                }
            }
            foreach ($status as $status_name) {
                if ($status_name > 0){
                    $this->status = $status_name;
                    $this->save();
                    return $status_name;
                }
            }
        } else {
            // 全款状态 
            return $status; 
        }
    }

    public  function  comment()
    {
        return $this->hasOne(CaseOrderComment::class, 'order_id', 'id');
    }
}

