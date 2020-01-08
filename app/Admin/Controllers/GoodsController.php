<?php

namespace App\Admin\Controllers;

use App\Model\Goods;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\{
    Form,
    Grid,
    Show
};
use Illuminate\Support\Facades\Storage;

class GoodsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商城';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Goods());

        $grid->column('id', __('Id'));
        $grid->column('title', __('Title'));
        $grid->column('total', __('Total'));
        $grid->column('tags', __('Tags'))->display(function(){
            return explode(',', $this->tags);
        })->label();
        $grid->column('credit', __('Credit'));
        $grid->column('price', __('Price'));
        $grid->column('thumb', __('Thumb'))->display(function(){
            $url = Storage::disk('admin')->url($this->thumb);
            return "<img src='{$url}' style='width:100px'/>";
        });
        $grid->column('updated_at', __('Updated at'));
        $grid->column('created_at', __('Created at'));

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
        $show = new Show(Goods::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('total', __('Total'));
        $show->field('tags', __('Tags'));
        $show->field('status', __('Status'));
        $show->field('content', __('Content'));
        $show->field('credit', __('Credit'));
        $show->field('price', __('Price'));
        $show->field('thumb', __('Thumb'));
        $show->field('updated_at', __('Updated at'));
        $show->field('created_at', __('Created at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Goods());

        $form->text('title', __('Title'))->rules('required');
        $form->number('total', __('Total'))
            ->default(1)
            ->rules('required|gt:0|int');
        $form->tags('tags', __('Tags') );
        $form->simditor('content', __('Content'))->rules('required');
        $form->number('credit', __('Credit'))->default(0);
        $form->decimal('price', __('Price'))->default(0);
        $form->image('thumb', __('Thumb'))->rules('required');

        return $form;
    }
}
