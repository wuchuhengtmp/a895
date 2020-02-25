<?php

namespace App\Admin\Actions\CaseOrder;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class Doing extends RowAction
{
    public $name = '进行中';

    public function handle(Model $model)
    {
        $model->status = 2;
        if ($model->save()) {
            return $this->response()->success('操作成功')->refresh();
        }  else {
            return $this->response()->error('操作失败')->refresh();
        }
    }

    public function form()
    {
        $this->text('balance', '尾款')
            ->rules('required|regex:/^[0-9]+(.[0-9]{1,2})?$/', [
                'required' => '尾款不能为空',
                'regex' => '尾款格式不正确'
            ]);
    }

}
