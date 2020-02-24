<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Validate\CheckCaseOrder;
use App\Http\Service\CaseOrder as CaseOrderService;

class CaseOrderController extends Controller
{
    /**
     * 生成项目订单
     */
    public function save(Request $Request)
    {
        (new CheckCaseOrder())->gocheck();
        $case_data = [
            'case_id'   => $Request->input('case_id'),
            'room'      => $Request->input('room'),
            'area'      => $Request->input('area'),
            'city_code' => $Request->input('city_code'),
            'phone'     => $Request->input('phone'),
            'name'      => $Request->input('name'),
            'pay_type'  => $Request->input('pay_type'),
            'user_id'   => $this->user()->id
        ];
        $trade = (new  CaseOrderService())->generateOrder($case_data);
        return $this->responseSuccessData($trade);
    }
}
