<?php

namespace App\Admin\Controllers;

use App\Model\CaseOrder;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CaseOrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '案例订单';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CaseOrder());

        $grid->column('id', __('Id'));
        $grid->column('user.nickname', __('User id'));
        $grid->column('prepay_price', __('Prepay price'));
        $grid->column('area', __('Area'));
        $grid->column('room', __('Room'));
        $grid->column('city.name', __('City code'));
        $grid->column('phone', __('Phone'));
        $grid->column('name', "联系人");
        $grid->column('status')
            ->display(function($field) {
                switch($field) {
                case 0 :
                 return    "未支付 ";
                case 1 :
                    return "支付 ";
                }
            })
            ->label([
                0 => 'warning',
                1 => 'default',
                2 => 'success',
                3 => 'info',
            ]);
        $grid->column('pay_type', __('Pay type'))
            ->display(function($field) {
                switch($field) {
                case 'wechat' :
                 return    "微信";
                case 'alipay' :
                    return "支付宝";
                }
            })
            ->label([
                'wechat' => 'success',
                'alipay' => 'info',
            ]);
        $grid->column('out_trade_no', __('Out trade no'));
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
        $show = new Show(CaseOrder::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('case_id', __('Case id'));
        $show->field('case_info', __('Case info'));
        $show->field('prepay_price', __('Prepay price'));
        $show->field('area', __('Area'));
        $show->field('room', __('Room'));
        $show->field('city_code', __('City code'));
        $show->field('phone', __('Phone'));
        $show->field('name', __('Name'));
        $show->field('status', __('Status'));
        $show->field('pay_type', __('Pay type'));
        $show->field('out_trade_no', __('Out trade no'));
        $show->field('prepay_id', __('Prepay id'));
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
        $form = new Form(new CaseOrder());

        $form->number('user_id', __('User id'));
        $form->number('case_id', __('Case id'));
        $form->textarea('case_info', __('Case info'));
        $form->decimal('prepay_price', __('Prepay price'));
        $form->text('area', __('Area'));
        $form->text('room', __('Room'));
        $form->text('city_code', __('City code'));
        $form->mobile('phone', __('Phone'));
        $form->text('name', __('Name'));
        $form->number('status', __('Status'));
        $form->text('pay_type', __('Pay type'));
        $form->text('out_trade_no', __('Out trade no'));
        $form->text('prepay_id', __('Prepay id'));

        return $form;
    }
}
