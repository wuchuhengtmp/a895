<?php

namespace App\Admin\Controllers;

use App\Model\PayTimes;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PayTimesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '分期申请';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PayTimes());
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
            $actions->disableDelete();
            if ($actions->row->status === 201) {

            }
        });

        $grid->disableCreateButton();

        $grid->column('id', __('Id'));
        $grid->column('order_id', __('Order Id'));
        $grid->column('total_price', '金额');
        $grid->column('status', __('Status'))->display(function($field){
            switch($field) {
            case 100:
                return '已支付';
            case 101:
                return '支付中';
            case 102:
                return '支付失败';
            case 103:
                return '逾期';
            case 104:
                return '未支付';
            }
        })->label([
            100 => 'default',
            101 => 'warning',
            102 => 'success',
            103 => 'info',
        ]);
        $grid->column('pay_at', __('Pay at'));
        $grid->column('reply', __('Reply'));
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
        $show = new Show(PayTimes::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('total_price', __('Total price'));
        $show->field('order_id', __('Order id'));
        $show->field('status', __('Status'));
        $show->field('pay_at', __('Pay at'));
        $show->field('reply', __('Reply'));
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
        $form = new Form(new PayTimes());

        $form->decimal('total_price', __('Total price'));
        $form->number('order_id', __('Order id'));
        $form->number('status', __('Status'));
        $form->datetime('pay_at', __('Pay at'))->default(date('Y-m-d H:i:s'));
        $form->text('reply', __('Reply'));

        return $form;
    }
}
