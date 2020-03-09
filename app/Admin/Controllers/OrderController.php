<?php

namespace App\Admin\Controllers;

use App\Model\Order;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Encore\Admin\Widgets\Table;
use App\Admin\Actions\Order\{
 ConfirmPay,
 ConfirmPost,
 ConfirmReceipt,
 Cancel
};
use App\Http\Service\Express;

class OrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '订单号';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order());
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableDelete();
            $actions->disableView();
            switch($actions->row->status) {
            case 0: 
                $actions->add(new ConfirmPay());
                break;
            case 1:
                $actions->add(new ConfirmPost());
            case 2:
                $actions->add(new ConfirmReceipt());
            }
        });

        $grid->column('id', __('Id'));
        $grid->column('out_trade_no', __('Out trade no'));
        $grid->column('total', __('Total'));
        $grid->column('pay_type', __('支付方式'))->display(function($field) {
            switch($field) {
                case 'alipay':
                    return '支付宝';
                    break;
                case 'wechat':
                    return '微信';
                    break;
            }
        })->label([
            'alipay' => 'success',
            'wechat' => 'warning'
        ]);
        $grid->column('status', __('Status'))->display(function($field){
            switch($field) {
                case -1 : 
                return '订单关闭';break;
                case 0 :
                    return '待支付';
                case 1:
                    return '待发货';
                case 2:
                    return '待收货';
                case 3:
                    return '完成';
            }
        })->label([
            0 => 'default',
            1 => 'info' ,
            2 => 'success',
            3 => 'warning'
        ]);
        $grid->column('total_price', __('Total price'));
        $grid->column('total_credit', __('Total credit'));
        $grid->column('title', __('商品'));
        $grid->column('recieve_info', __('收货人信息'))
            ->display(function(){
                return '收货详情';
            })
            ->expand(function ($model) { 
                $addresses = json_decode($this->address_info);
                $City = DB::table('china_area')->where('code', $addresses->city_code)->select('name')->first();
                $addresses->city_code = $City->name;
                unset($addresses->id, $addresses->user_id,$addresses->is_default);
                $addresses = (array)$addresses;
                $this->express_no || $this->express_no = '(没有订单号)';
                array_unshift($addresses, $this->express_no);
                return new Table(['快递号', '姓名', '地址', '手机', '城市'], [$addresses]);
            });
        $grid->column('express_no', __('Express_info'))
            ->display(function() {
                if ($this->status > 1) {
                    return '详情';
                } else {
                    return '(暂无详情)';
                }
            })
            ->expand(function ($model) { 
                if ($this->status > 1) {
                    $Expresses = new Express();
                    try {
                        $ExpressInfo = $Expresses->getExpressInfoByNo($this->express_no, $this->express_co);
                    } catch(\Exception $E) {
                        return '暂无快递信息';
                    }
                    $Addresses = json_decode($this->address_info);
                    switch($ExpressInfo->result->deliverystatus) {
                        case 0:
                            $delivery_msg = '快递收件(揽件)';break; 
                        case 1:
                            $delivery_msg = '在途中 ';break;
                        case 2:
                            $delivery_msg = '正在派件 ';break;
                        case 3:
                            $delivery_msg = '已签收 ';break;
                        case 4:
                            $delivery_msg = '派送失败 ';break;
                        case 5:
                            $delivery_msg =  '疑难件 ';break;
                        case 6:
                            $delivery_msg = '退件签收';break;
                    }
                    $Table1 = new Table(['物流公司', '物流号', '收件人', '状态'], [[
                        $ExpressInfo->result->expName, 
                        $ExpressInfo->result->number,
                        $Addresses->name,
                        $delivery_msg
                    ]]);
                    $Table2 = null;
                    if (isset($ExpressInfo->result->list)) {
                        $list = [];
                        foreach($ExpressInfo->result->list as $el) {
                            $tmp = [];
                            $tmp['item'] = $el->status;
                            $tmp['time'] = $el->time;
                            $list[] = $tmp;
                        }
                        $Table2 = new Table(['状态', '时间'], $list);
                    }
                    return $Table1 . $Table2;
                } else {
                    return '暂无物流消息';
                }
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
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('deleted_at', __('Deleted at'));

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

        return $form;
    }
}
