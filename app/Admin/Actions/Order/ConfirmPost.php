<?php

namespace App\Admin\Actions\Order;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use App\Model\Express;

class ConfirmPost extends RowAction
{
    public $name = '确认发货';

    public function handle(Model $model)
    {
        $model->express_no = trim(request()->express_no);
        $model->express_co = trim(request()->express_co);
        $model->status = 2;
        if ($model->save()) {
            return $this->response()->success('操作成功')->refresh();
        } else {
            return $this->response()->error('操作失败')->refresh();
        }
    }

    public function form()
    {
        $Expresses = (new Express())->select('name', 'type')->get();
        $selectes = [];
        foreach($Expresses as $el) {
            $selectes[$el->type] = $el->name;
        }
        $this->text('express_no', __('Express_no'))
            ->rules('required', [
                'required' => '请输入订单号'
            ]);
        $this->select('express_co', __('Express_co'))
            ->options($selectes) 
            ->rules('required', [
                'required' => '请选择快递公司'
            ]);
    }
}
