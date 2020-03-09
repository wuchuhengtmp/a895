<?php

namespace App\Admin\Controllers;

use App\Model\Order;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Admin\Actions\Order\Refund;

class RefundController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '申请退款';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order());

        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
            $actions->disableDelete();
            if (!in_array($actions->row->status, [1])) {
                $actions->add(new Refund());
            }
        });
        $grid->model()->where('refund_status', '!=', 0);
        $grid->column('id', __('Id'));
        $grid->column('out_trade_no', __('Out trade no'));
        $grid->column('refund_status', __('Refund status'))
        ->display(function($refund_status){
            switch($refund_status) {
            case -1 :
                return '失败';
            case 1:
                return '申请中';
            case 2:
                return '退款成功';
            }
        })
        ->label([
            -1 => 'info',
            0 => 'success',
            1 => 'warning',
            2 => 'error'
        ]);
        $grid->column('content', __('Content'));
        $grid->column('refund_thumb', __('Refund thumb'))->lightbox();
        $grid->column('refund_reply', __('refund reply'));
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
        $show = new Show(Order::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('out_trade_no', __('Out trade no'));
        $show->field('user_id', __('User id'));
        $show->field('goods_id', __('Goods id'));
        $show->field('total', __('Total'));
        $show->field('pay_type', __('Pay type'));
        $show->field('address_info', __('Address info'));
        $show->field('pay_at', __('Pay at'));
        $show->field('status', __('Status'));
        $show->field('total_price', __('Total price'));
        $show->field('total_credit', __('Total credit'));
        $show->field('alipay_trade_no', __('Alipay trade no'));
        $show->field('express_no', __('Express no'));
        $show->field('title', __('Title'));
        $show->field('prepay_id', __('Prepay id'));
        $show->field('app_pay_sign', __('App pay sign'));
        $show->field('goods_info', __('Goods info'));
        $show->field('express_co', __('Express co'));
        $show->field('goods_stars', __('Goods stars'));
        $show->field('service_stars', __('Service stars'));
        $show->field('refund_reply', __('refund reply'));
        $show->field('express_stars', __('Express stars'));
        $show->field('is_comment', __('Is comment'));
        $show->field('refund_status', __('Refund status'));
        $show->field('content', __('Content'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('deleted_at', __('Deleted at'));
        $show->field('refund_thumb', __('Refund thumb'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Order());

        $form->text('out_trade_no', __('Out trade no'));
        $form->number('user_id', __('User id'));
        $form->number('goods_id', __('Goods id'));
        $form->number('total', __('Total'));
        $form->text('pay_type', __('Pay type'));
        $form->text('address_info', __('Address info'));
        $form->datetime('pay_at', __('Pay at'))->default(date('Y-m-d H:i:s'));
        $form->switch('status', __('Status'));
        $form->decimal('total_price', __('Total price'));
        $form->number('total_credit', __('Total credit'));
        $form->text('alipay_trade_no', __('Alipay trade no'));
        $form->text('express_no', __('Express no'));
        $form->text('title', __('Title'));
        $form->text('prepay_id', __('Prepay id'));
        $form->text('app_pay_sign', __('App pay sign'));
        $form->text('goods_info', __('Goods info'));
        $form->text('express_co', __('Express co'));
        $form->number('goods_stars', __('Goods stars'));
        $form->number('service_stars', __('Service stars'));
        $form->textarea('refund_reply', __('Refund reply'));
        $form->number('express_stars', __('Express stars'));
        $form->number('is_comment', __('Is comment'));
        $form->number('refund_status', __('Refund status'));
        $form->textarea('content', __('Content'));
        $form->text('refund_thumb', __('Refund thumb'));

        return $form;
    }
}
