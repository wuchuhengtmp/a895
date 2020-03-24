<?php

namespace App\Admin\Actions\Subject;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class Addgoods extends RowAction
{
    public $name = '添加商品';

    public function href()
    {
        $id = ($this->row->id);
        return "/admin/goods/create?goods_id=" . $id;
    }
}
