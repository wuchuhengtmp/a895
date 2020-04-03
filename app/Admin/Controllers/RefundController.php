<?php

namespace App\Admin\Controllers;

use App\Model\CaseOrder as Order;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Admin\Actions\CaseOrder\Refund;

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
            if($actions->row->status != 500 ) {
                $actions->add(new Refund());
            }
        });
        $grid->model()->where('status', '>=', 500);
        $grid->column('id', __('Id'));
        $grid->column('title', __('title'));
        $grid->column('refund_content', __('refund_content'));
        $grid->column('reply', __('refund reply'));
        $grid->column('status', __('status'))
            ->display(function($field) {
                switch($field) {
                case 500 :
                    return '成功';
                case 501 :
                    return '申请中';
                case 502 :
                    return '失败';
                }
            }) ->label([
                500 => 'warning',
                501 => 'success',
                502 => 'success'
            ]);
        $grid->column('image', __('image'))->lightbox();
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
        $form->text('refund_thumb', __('Refund thumb'));

        return $form;
    }
}
