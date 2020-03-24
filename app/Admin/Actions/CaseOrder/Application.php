<?php

namespace App\Admin\Actions\CaseOrder;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use App\Model\PayTimes;

class Application extends RowAction
{
    public $name = '订单申请';
    public $times = [
            1 => '一期',
            2 => '二期',
            3 => '三期',
            4 => '四期',
            5 => '五期',
            6 => '六期',
            7 => '七期',
            8 => '八期',
            9 => '九期',
            10 => '十期',
            11 => '十一期',
            12 => '十二期',
            13 => '十三期',
            14 => '十四期',
        ];

    public function handle(Model $model)
    {
        $model->status  = request()->status;
        $model->balance = request()->balance;
        if (request()->has('reply')) $model->reply = request()->reply;
        if ($model->app_pay_type === 'installment' && request()->status == 200) {
                $year   = $model->created_at->format("Y");
                $month  = +$model->created_at->format("m");
                $day    = get_config('PAY_DAY');
                $time_price = round($model->balance / $model->times, 2);
            for($i = 1; $i <= $model->times; $i++) {
                $month++;
                if ($month > 12) {
                    $year++;
                    $month = 1;
                }
                $PayTimes = new PayTimes();
                $PayTimes->status = 104;
                $PayTimes->total_price = $time_price;
                $PayTimes->pay_at = "{$year}-{$month}-{$day} 00:00:00";
                $PayTimes->order_id = $model->id;
                $PayTimes->save();
            }
        }
        if ($model->save()) {
            return $this->response()->success('操作成功')->refresh();
        } else {
            return $this->response()->error('操作失败')->refresh();
        }
    }

    public function form()
    {
        $this->text('balance', '尾款')
            ->rules('required_if:status,200|regex:/^[0-9]+(.[0-9]{1,2})?$/', [
                'required_if' => '尾款不能为空',
                'regex' => '尾款格式不正确'
            ]);
        $type = [
            200 => '申请成功',
            202 => '申请失败',
        ];
        $this->select('status', '状态')->default(200)->options($type)->rules('required', ['required' => '订单状态不能为空']);

        $this->textarea('reply', '原因')
            ->rules('required_if:status,202', [
                'required_if' => '申请失败的原因不能为空',
            ]);
    }

}
