<?php

namespace App\Admin\Controllers;

use App\Model\Task;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TaskController extends AdminController
{
    protected $title = '做任务领积分设置';

    protected function grid()
    {
        $grid = new Grid(new Task());

        $grid->model();

        $grid->id('ID');
        $grid->title('标题');
        $grid->credit('奖励积分');

        $grid->actions(function ($actions) {
            // 去掉删除
            $actions->disableDelete();
        });

        // 去掉新增按钮
        $grid->disableCreateButton();
        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(Task::findOrFail($id));

        $show->id('ID');
        $show->title('标题');
        $show->credit('奖励积分');

        $show->panel()->tools(function ($tools){
            $tools->disableDelete();
        });
        return $show;
    }

    protected function form()
    {
        $form = new Form(new Task());

        // 正则验证：rules();数据重复验证：creationRules() updateRules();
        $form->number('credit','奖励积分')->min(0);

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
        });

        return $form;
    }

}
