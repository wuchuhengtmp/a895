<?php

namespace App\Admin\Controllers;

use App\Model\Subject;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Admin\Actions\Subject\Addgoods;


class SubjectController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商品专题';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Subject());
        $grid->actions(function ($actions) {
            $actions->add(new Addgoods);
        });
        $grid->column('id', __('Id'));
        $grid->column('subject', __('Subject'));
        $grid->column('thumb', __('Thumb'))->image();
        $grid->column('redirection', __('redirection'))->editable();
        $grid->column('count', '商品数量')->display(function($val){
            return "<a href='/admin/goods?subject_id=".$this->id."'>".$val."</a>";
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Subject::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('subject', __('Subject'));
        $show->field('thumb', __('Thumb'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Subject());

        $form->text('subject', __('Subject'));
        $form->image('thumb', __('Thumb'));
        $form->text('redirection', __('redirection'));
        $states = [
            'on'  => ['value' => 1, 'text' => '打开', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '关闭', 'color' => 'danger'],
        ];
        $form->switch('has_banner', '使用banner图')->states($states);
        return $form;
    }
}
