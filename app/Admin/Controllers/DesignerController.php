<?php

namespace App\Admin\Controllers;

use App\Model\Designer;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Storage;

class DesignerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '设计师';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Designer());

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('service_city_code', __('service_city_code'))->editable();
        $grid->column('min_price', __('min_price'))->editable();
        $grid->column('max_price', __('max_price'))->editable();
        $grid->column('prepay_price', __('prepay_price'))->editable();
        $grid->column('is_dispatch', __('is_dispatch'))->editable();
        
        $grid->column('longitude', __('Longitude'));
        $grid->column('latitude', __('Latitude'));
        $grid->column('avatar', __('Avatar'))->display(function(){
            return "<img src='" . Storage::disk('admin')->url($this->avatar) . "' style='width:50px'/>";
        });
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
        $show = new Show(Designer::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('longitude', __('Longitude'));
        $show->field('latitude', __('Latitude'));
        $show->field('avatar', __('Avatar'));
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
        $form = new Form(new Designer());

        $form->text('name', __('Name'));
        $form->image('avatar', __('Avatar'));

        return $form;
    }
}
