<?php

namespace App\Admin\Controllers;

use App\Model\CaseOrderComment;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Storage;

class CaseOrderCommentController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '案例订单评价';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CaseOrderComment());

        $grid->column('id', __('Id'));
        if (request()->order_id) {
            $grid->model()->where('order_id', request()->order_id);
        }
        $grid->column('service_stars', '服务评分')
            ->display(function ($rate) {
                $html = "<i class='fa fa-star' style='color:#ff8913'></i>";
                if ($rate < 1) {
                    return '';
                }
                return join('&nbsp;', array_fill(0, min(5, $rate), $html));
            });
        $grid->column('design_stars', __('设计评分'))
            ->display(function ($rate) {
                $html = "<i class='fa fa-star' style='color:#ff8913'></i>";
                if ($rate < 1) {
                    return '';
                }
                return join('&nbsp;', array_fill(0, min(5, $rate), $html));
            });
        $grid->column('material_stars', __('材料评分'))
            ->display(function ($rate) {
                $html = "<i class='fa fa-star' style='color:#ff8913'></i>";
                if ($rate < 1) {
                    return '';
                }
                return join('&nbsp;', array_fill(0, min(5, $rate), $html));
            });
        $grid->column('content', __('Content'));
        $grid->column('img', __('截图'))->display(function($field) {
            $url = Storage::disk('img')->url($field);
            return $url;
        })->lightbox(['height' => 50]);
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
        $show = new Show(CaseOrderComment::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('order_id', __('Order id'));
        $show->field('business_stars', __('Business stars'));
        $show->field('service_stars', __('Service stars'));
        $show->field('design_stars', __('Design stars'));
        $show->field('material_stars', __('Material stars'));
        $show->field('content', __('Content'));
        $show->field('img', __('Img'));
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
        $form = new Form(new CaseOrderComment());

        $form->number('order_id', __('Order id'));
        $form->number('business_stars', __('Business stars'));
        $form->number('service_stars', __('Service stars'));
        $form->number('design_stars', __('Design stars'));
        $form->number('material_stars', __('Material stars'));
        $form->text('content', __('Content'));
        $form->image('img', __('Img'));

        return $form;
    }
}
