<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Validate\CheckCaseOrder;
use App\Exceptions\Api\Base as BaseException;
use Illuminate\Support\Facades\Validator;
use App\Model\{
    CaseOrder,
    CaseOrderComment as CaseOrderCommentModel
};
use App\Http\Service\{
    CaseOrder as CaseOrderService
};
use Illuminate\Support\Facades\DB;

class CaseOrderController extends Controller
{
    public function index(Request $Request, CaseOrder $CaseOrder)
    {
        (new CheckCaseOrder())->scene('get_casse_orders')->gocheck();
        $return_arr = [
            'list'  => [],
            'total' => 0
        ];
        $status = [];
        switch($Request->status)
        {
            case 'doing':
                $status = [100, 200, 201, 202, 203, 301, 302, 303];
                break;
            case 'finished':
                $status = [300];
                break;
            case 'feedback':
                $status  = [400];
                break;
        }
        $CaseOrders = $CaseOrder->whereIn('status', $status)
            ->where('user_id', $this->user()->id)
            ->paginate(10);
        foreach($CaseOrders as $CaseOrder) {
            $tmp = [];
            $tmp['id'] = $CaseOrder->id;
            $Case  = json_decode($CaseOrder->case_info);
            $tmp['thumb_url'] = get_absolute_url($Case->thumb_url);
            $tmp['title'] = $CaseOrder->title;
            $tmp['prepay_price'] = $CaseOrder->prepay_price;
            $tmp['balance'] = $CaseOrder->balance;
            $tmp['reply'] = (string)$CaseOrder->reply;
            $tmp['status'] = (new CaseOrderService())->getStatusById($CaseOrder->id);
            $return_arr['list'][] = $tmp;
        }
        $return_arr['total'] = $CaseOrders->total();
        $return_arr['lastpage'] = $CaseOrders->lastPage();
        return $this->responseSuccessData($return_arr);
    }

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
            'user_id'   => $this->user()->id
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
        if ($Request->app_pay_type === 'installment') {
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

    /**
     * 保存评论
     *
     */
    public function saveComment(
        Request $Request,
        CheckCaseOrder $CheckCaseOrder,
        CaseOrderCommentModel $CaseOrderCommentModel,
        CaseOrder $CaseOrderModel
    )
    {
        $CheckCaseOrder->scene('save_comment')->gocheck();
        $CaseOrderCommentModel->order_id       = $Request->id;
        $CaseOrderCommentModel->business_stars = $Request->business_stars;
        $CaseOrderCommentModel->service_stars  = $Request->service_stars;
        $CaseOrderCommentModel->design_stars   = $Request->design_stars;
        $CaseOrderCommentModel->material_stars = $Request->material_stars;
        $CaseOrderCommentModel->content        = $Request->content;
        $CaseOrderCommentModel->img            = $Request->img;
        $CaseOrder = $CaseOrderModel->where('id', $Request->id)->first(); 
        $CaseOrder->status = 400;
        DB::beginTransaction();
        try{
            $CaseOrderCommentModel->save();
            $CaseOrder->save();
            DB::commit();
            return $this->responseSuccess(); 
        } catch(\Exception $E) {
            DB::rollBack();
            return $this->responseFail();
        }
    }

    /**
     * 删除
     *
     */
    public function destroy(Request $Request, CaseOrder $CaseOrderModel)
    {
        (new CheckCaseOrder())->scene('delete_casse_order')->gocheck();
        if ($CaseOrderModel->where('id', $Request->id)->delete()) {
            return $this->responseSuccess();
        } else {
            return $this->responseFail();
        }
    }
}
