<?php

namespace App\Admin\Actions\Order;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class ConfirmReceipt extends RowAction
{
    public $name = '确认收货';

    public function handle(Model $model)
    {
        $model->status = 3;
        if ($model->save()) {
            return $this->response()->success('操作成功')->refresh();
        } else {
            return $this->response()->error('操作失败')->refresh();
        }
    }

    public function dialog()
    {
        $this->confirm('确定收货吗？');
    }

}
