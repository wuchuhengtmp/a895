<?php

namespace App\Admin\Actions\PayTimes;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class ComfirnPay extends RowAction
{
    public $name = '确认支付';

    public function handle(Model $model)
    {
        // $model ...

        return $this->response()->success('Success message.')->refresh();
    }

}