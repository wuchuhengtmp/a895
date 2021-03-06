<?php

namespace App\Admin\Actions\PayTimes;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use App\Http\Service\CaseOrder;

class ComfirnPay extends RowAction
{
    public $name = '支付审核';

    public function handle(Model $model)
    {
        switch(request()->status) {
            case 100:
                $model->status = 100;
                $model->reply = '';
                break;
            case 102:
                $model->status = 102;
                $model->reply = request()->reply;
                break;
        }
        $model->caseOrder->reply = request()->reply;
        
        if ($model->save()) {
            $model->caseOrder->status = (new CaseOrder())->getStatusById($model->caseOrder->id);
            $model->caseOrder->save();
            return $this->response()->success('操作成功')->refresh();
        } else {
            return $this->response()->error('操作失败')->refresh();
        }
    }

    public function form()
    {
        $type = [
            100 => '成功',
            102 => '失败',
        ];
        $this->select('status', '审核')->default(100)->options($type)->rules('required', ['required' => '审核状态不能为空']);

        $this->textarea('reply', '失败原因')
            ->rules('required_if:status,102', [
                'required_if' => '申请失败的原因不能为空',
            ]);
    }
}
