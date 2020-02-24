<?php

namespace App\Http\Controllers\Api;

use function Couchbase\defaultDecoder;
use Illuminate\Http\Request;
use App\Http\Validate\{
    CheckUserExists
};
use App\Http\Service\{
    MeOrder as MeOrderService
};

class MeOrderController extends Controller
{
    /**
     * 获取订单列表
     *
     */
    public function getOrderList($type)
    {
        (new CheckUserExists())->gocheck();
        $list = (new MeOrderService())->getOrderList($this->user()->id,$type);
        return $this->responseSuccessData($list);
    }

    /**
     * 关闭或删除订单
     *
     */
    public function orderDelete($id)
    {
        (new CheckUserExists())->gocheck();
        (new MeOrderService())->orderDelete($id);
        return $this->responseSuccess();
    }

    /**
     * 查看物流信息
     *
     */
    public function orderLogistics($id)
    {
        (new CheckUserExists())->gocheck();
        $list = (new MeOrderService())->orderLogistics($id);
        return $this->responseSuccessData($list);
    }

    /**
     * 确认收货
     *
     */
    public function orderConfirm($id)
    {
        (new CheckUserExists())->gocheck();
        (new MeOrderService())->orderConfirm($this->user()->id,$id);
        return $this->responseSuccess();
    }

    /**
     * 订单详情
     *
     */
    public function orderInfo($id)
    {
        (new CheckUserExists())->gocheck();
        $list = (new MeOrderService())->orderInfo($id);
        return $this->responseSuccessData($list);
    }

    /**
     * 评价商品
     *
     */
    public function evaluateGood()
    {
        (new CheckUserExists())->gocheck();
        (new MeOrderService())->evaluateGood($this->user()->id);
        return $this->responseSuccess();
    }

}
