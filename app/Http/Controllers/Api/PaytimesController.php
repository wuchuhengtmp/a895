<?php

/**
 * 分期资源控制器
 *
 */
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Service\{
    CaseOrder as CaseOrderService,
    PayTimes as PayTimesService
};
use App\Http\Validate\{
    CheckPayTimes ,
    CheckCaseOrder
};
use App\Model\{
    PayTimes as PayTimesModel
};

class PaytimesController extends Controller
{
    /**
     * 分期列表 
     *
     */
    public function index(Request $Request, CaseOrderService $CaseOrderService)
    {
        (new CheckPayTimes())->scene('get_pay_times_list')->gocheck();
        $pay_times_list = $CaseOrderService->getPayTimesById($Request->order_id);
        return $this->responseSuccessData($pay_times_list);
    }

    /**
     * 分期申请
     * 
     * @http patch
     */
    public function update(Request $Request, PayTimesModel $PayTimesModel, PayTimesService $PayTimesService)
    {
        (new CheckPayTimes())->scene('application')->gocheck();
        $PayTimesModel->where('id', $Request->id)->first();
        $data = [
            'id' => $Request->id,
            'image1' => $Request->image1,
            'image2' => $Request->image2,
        ];
        $is_success = $PayTimesService->recordApplication($data);
        return $is_success ? $this->responseSuccess() : $this->responseFail();
    }
}
