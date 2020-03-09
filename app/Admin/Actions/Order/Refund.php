<?php

namespace App\Admin\Actions\Order;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class Refund extends RowAction
{
    public $name = '退款申请';

    public function handle(Model $model)
    {
        $model->refund_status = request()->refund_status;
        if (request()->refund_reply) {
            $model->refund_reply = request()->refund_reply;
        }
        if($model->save()) {
            return $this->response()->success('操作成功')->refresh();
        } else {
            return $this->response()->error('操作失败')->refresh();
        }
    }

    public function form()
    {
        $options = [
            2  => '通过',
            -1 => '失败'
        ];
        $this->select('refund_status', __('Refund status'))
            ->options($options) 
            ->rules('required', [
                'required' => '请选择状态'
            ]);
        $this->text('refund_reply', __('refund reply'))
            ->rules('required_if:refund_status,-1', [
                '请求输入退款回复'
            ]);
    }

}
