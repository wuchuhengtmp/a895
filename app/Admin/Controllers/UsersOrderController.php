<?php

namespace App\Admin\Controllers;

use App\Model\Order;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UsersOrderController extends AdminController
{
    protected $title = '用户商品订单';

    protected function grid()
    {
        $grid = new Grid(new Order());

        $grid->model()->where('status',1)->orderBy('pay_at','desc');

        $grid->id('ID');
        $grid->pay_at('支付时间');
        $grid->title('商品名');
        $grid->num('购买数量');
        $grid->name('收货人姓名')->display(function (){
            $name = json_decode($this->address_info,true);
            return $name['name'];
        });
        $grid->phone('收货人联系电话')->display(function (){
            $phone = json_decode($this->address_info,true);
            return $phone['phone'];
        });
        $grid->address('收货地址')->display(function (){
            $address = json_decode($this->address_info,true);
            return $address['address'];
        });
        $grid->logistics_number('物流单号');

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
        $show = new Show(Order::findOrFail($id));

        $show->id('ID');
        $show->pay_at('支付时间');
        $show->title('商品名');
        $show->num('购买数量');
        $show->name('收货人姓名')->as(function (){
            $name = json_decode($this->address_info,true);
            return $name['name'];
        });
        $show->phone('收货人联系电话')->as(function (){
            $phone = json_decode($this->address_info,true);
            return $phone['phone'];
        });
        $show->address('收货地址')->as(function (){
            $address = json_decode($this->address_info,true);
            return $address['address'];
        });
        $show->logistics_number('物流单号');

        $show->panel()->tools(function ($tools){
            $tools->disableDelete();
        });
        return $show;
    }

    protected function form()
    {
        $form = new Form(new Order());

        // 正则验证：rules();数据重复验证：creationRules() updateRules();
        $form->text('logistics_number','物流单号');

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
        });

        return $form;
    }

}