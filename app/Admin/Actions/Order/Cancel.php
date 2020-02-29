<?php

namespace App\Admin\Actions\Order;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class Cancel extends RowAction
{
    public $name = '取消订单';

    public function handle(Model $model)
    {
        dd($model->status);
        $model->status = -1;
        if ($model->save()) {
            return $this->response()->success('操作成功')->refresh();
        } else {
            return $this->response()->error('操作失败')->refresh();
        }
    }

    public function dialog()
    {
        return  '确定取消订单';
    }
}
