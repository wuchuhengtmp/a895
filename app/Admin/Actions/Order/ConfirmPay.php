<?php

namespace App\Admin\Actions\Order;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class ConfirmPay extends RowAction
{
    public $name = '确认支付';

    public function handle(Model $model)
    {
        $model->status = 1;
        if ($model->save()) {
            return $this->response()->success('操作成功')->refresh();
        } else {
            return $this->response()->error('操作失败');
        }
    }

     public function dialog()
     {
        $this->confirm('确定支付？');
     }
}
