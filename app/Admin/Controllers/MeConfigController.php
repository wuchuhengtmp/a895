<?php

namespace App\Admin\Controllers;

use App\Model\MeConfig;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MeConfigController extends AdminController
{
    protected $title = '关于我们';

    protected function grid()
    {
        $grid = new Grid(new MeConfig());

        $grid->model();

        $grid->id('ID');
        $grid->title('标题');
        $grid->content('内容');

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
        $show = new Show(MeConfig::findOrFail($id));

        $show->id('ID');
        $show->title('标题');
        $show->content('内容');

        $show->panel()->tools(function ($tools){
            $tools->disableDelete();
        });
        return $show;
    }

    protected function form()
    {
        $form = new Form(new MeConfig());

        // 正则验证：rules();数据重复验证：creationRules() updateRules();
        $form->textarea('content','内容');

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
        });

        //保存前回调
        $form->saving(function (Form $form) {
            if($form->model()->title == '用户协议'){
                $form->model()->type = 0;
            }else{
                $form->model()->type = 1;
            }
        });

        return $form;
    }

}