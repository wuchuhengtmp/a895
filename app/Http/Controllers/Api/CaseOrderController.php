<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Validate\CheckCaseOrder;
use App\Exceptions\Api\Base as BaseException;
use Illuminate\Support\Facades\Validator;
use App\Model\CaseOrder;
use App\Http\Service\{
    CaseOrder as CaseOrderService
};

class CaseOrderController extends Controller
{
    /**
     * 生成项目订单
     */
    public function save(Request $Request)
    {
        (new CheckCaseOrder())->scene('create_order')->gocheck();
        $case_data = [
            'case_id'   => $Request->input('case_id'),
            'room'      => $Request->input('room'),
            'area'      => $Request->input('area'),
            'city_code' => $Request->input('city_code'),
            'phone'     => $Request->input('phone'),
            'name'      => $Request->input('name'),
            'pay_type'  => $Request->input('pay_type'),
            /* 'user_id'   => $this->user()->id */
            'user_id'   => 12
        ];
        $trade = (new  CaseOrderService())->generateOrder($case_data);
        return $this->responseSuccessData($trade);
    }

    /**
     *  订单详情
     *
     */
    public function show(Request $Request, CaseOrder $CaseOrderModel)
    {
        (new CheckCaseOrder())->scene('get_order')->gocheck();
        $order_info = (new CaseOrderService())->getOrderById($Request->id);
        return $this->responseSuccessData($order_info);
    }

    /**
     *  提交申请
     *
     */
    public function update(Request $Request, CaseOrder $CaseOrder)
    {
        (new CheckCaseMustBeExists())->gocheck();
        $CaseOrder = $CaseOrder->where('id', $Request->id)->first();
    }

}
