<?php

namespace App\Admin\Actions\CaseOrder;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class Refund extends RowAction
{
    public $name = '退款申请';

    public function handle(Model $model)
    {
        if (request()->status == 500) {
            $model->reply = request()->reply;
        }
        $model->status = request()->status;
        if ($model->save()) {
            return $this->response()->success('操作成功')->refresh();
        } else {
            return $this->response()->error('操作失败')->refresh();
        }
    }

    public function form()
    {
        $options = [
            500  => '通过',
            502 => '不通过'
        ];
        $this->select('status', __('Refund status'))
            ->options($options) 
            ->rules('required', [
                'required' => '请选择状态'
            ]);
        $this->text('reply', __('refund reply'))
            ->rules('required_if:refund_status,-1', [
                '请求输入退款回复'
            ]);
    }
}
