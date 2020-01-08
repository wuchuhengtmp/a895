<?php

namespace App\Admin\Controllers;

use App\Model\Slide;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Storage;

class SlideController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '幻灯片';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Slide());
        $grid->model()->orderBy('order_num', 'asc');

        $grid->column('id', __('Id'));
        $grid->column('path', __('Path'))
            ->display(function() { 
                $url = Storage::disk('admin')->url($this->path);
                return "<img src='" . $url . "' width = '100'/>";
            });
        $grid->column('url', __('Url'))->editable();
        $grid->column('order_num', __('Order num'))->editable();
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(Slide::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('url', __('Url'));
        $show->field('href', __('Href'));
        $show->field('order_num', __('Order num'));
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
        $form = new Form(new Slide());

        $form->url('url', __('Url'));
        $form->image('path', __('Path'));
        $form->number('order_num', __('Order num'))
            ->default(0)
            ->rules('required|gt:-1|numeric|int', [
                'gt'   => '不能小于0',
                'int' => '必须为整数'
            ]);;

        return $form;
    }
}
