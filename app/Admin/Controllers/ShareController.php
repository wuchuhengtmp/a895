<?php

namespace App\Admin\Controllers;

use App\Model\Config;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ShareController extends AdminController
{
    protected $title = '分享设置';

    protected function grid()
    {
        $grid = new Grid(new Config());

        $grid->model()->whereIn('name',['share_url','user_credit','get_credit']);

        $grid->id('ID');
        $grid->notice('标题');
        $grid->value('值');

        $grid->actions(function ($actions) {
            // 去掉删除
            $actions->disableDelete();

            // 去掉查看
            $actions->disableView();
        });

        // 去掉新增按钮
        $grid->disableCreateButton();
        return $grid;
    }

    protected function form()
    {
        $form = new Form(new Config());

        // 正则验证：rules();数据重复验证：creationRules() updateRules();
        $form->text('value','值');

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            // 去掉`查看`按钮
            $tools->disableView();
        });

        //保存前回调
//        $form->saving(function (Form $form) {
//            Config::where('name','user_credit')->update(['value'=>($form->user_credit)]);
//            Config::where('name','get_credit')->update(['value'=>($form->get_credit)]);
//        });

        return $form;
    }

}