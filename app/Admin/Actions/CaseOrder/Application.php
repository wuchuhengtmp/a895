<?php

namespace App\Admin\Actions\CaseOrder;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class Application extends RowAction
{
    public $name = '订单申请';

    public function handle(Model $model)
    {
        // $model ...

        return $this->response()->success('Success message.')->refresh();
    }

}