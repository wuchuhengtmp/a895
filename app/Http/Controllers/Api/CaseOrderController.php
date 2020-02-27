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
        (new Checkcaseorder())->scene('get_order')->gocheck();
        $order_info = (new CaseOrderService())->getOrderById($Request->id);
        return $this->responseSuccessData($order_info);
    }

    /**
     *  提交申请
     *
     */
    public function update(Request $Request, CaseOrder $CaseOrder)
    {
        (new Checkcaseorder())->scene('verify_application')->gocheck();
        $CaseOrder = $CaseOrder->where('id', $Request->id)->first();
        $CaseOrder->status = 201;
        $CaseOrder->app_pay_type = $Request->app_pay_type;
        $CaseOrder->compact_url  = $Request->compact_url;
        if ($Request->has('times')) {
            $CaseOrder->times= $Request->times;
        }
        return $CaseOrder->save() ? $this->responseSuccess() : $this->responseFail();
    }

    /**
     * 订单支付(全款)
     *
     */
    public function pay(Request $Request, CheckCaseOrder $CheckCaseOrder, CaseOrderService $CaseOrderService)
    {
        $CheckCaseOrder->scene('pay')->gocheck();
        $is_success = $CaseOrderService->recordTotallPay([
            'id' => $Request->id,
            'image1' =>  $Request->image1,
            'image2' => $Request->image2
        ]);
        return $is_success ? $this->responseSuccess() : $this->responseFail();
    }
}
