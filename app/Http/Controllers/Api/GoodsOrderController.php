<?php
/**
 * 商品订单
 *
 */
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Validate\{
    CheckGoodsOrder
};
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Api\{
    Base as BaseException
};
use App\Model\{
    Order,
    Address,
    ChinaArea,
    GoodsComment
};

use Illuminate\Support\Facades\Storage;
use App\Http\Service\{
    Express,
    Pay as PayService
};
use Illuminate\Support\Facades\DB;

class GoodsOrderController extends Controller
{
    /**
     * 订单列表
     *
     */
   public function index(Order $OrderModel, Request $Request, GoodsComment $GoodsComment)
   {
       (new CheckGoodsOrder())->scene('get_order_list')->goCheck();
       $return_arr = ['list' => [], 'total' => 0];
       $Orders = $OrderModel->where('user_id', $this->user()->id)->whereIn('status', explode(',', $Request->status))->paginate();
       foreach($Orders as $Order) {
           $order_info = [];
           $order_info = $Order->only('id', 'title', 'total_price', 'total_credit', 'status', 'goods_info', 'total');
           $goods_info = json_decode($order_info['goods_info'], true);
           $thumb = Storage::disk('img')->url($goods_info['thumb']);
           unset($order_info['goods_info']);
           $Comment = $GoodsComment->where('order_id', $Order->id)->get();
           if ($Comment->isEmpty()) {
               $order_info['is_comment'] = 0;
           } else {
               $order_info['is_comment'] = 1;
           }
           $order_info['thumb'] = $thumb;
           $return_arr['list'][] = $order_info;
       }
       $return_arr['total'] = $Orders->lastPage();
       $return_arr['lastpage'] = $Orders->lastPage();
       return $this->responseSuccessData($return_arr);
   } 

   /**
     * 订单物流
     *
     */
    public function expressShow(
        Request $Request,
        Order $OrderModel,
        Express $ExpressesService,
        ChinaArea $ChinaArea
    )
    {
        $return_arr = [];
        (new CheckGoodsOrder())->scene('get_order_express')->goCheck();
        $Order = $OrderModel->where('id', $Request->id)->first();
        $return_arr['id']         = $Order->id;
        $return_arr['thumb']      = Storage::disk('img')->url(json_decode($Order->goods_info)->thumb);
        $return_arr['title']      = $Order->title;
        $return_arr['express_no'] = $Order->express_no;
        $return_arr['express_co'] = $Order->express->name;
        $return_arr['has_express'] = 1;
        try{
            $Express = $ExpressesService->getExpressInfoByNo($Order->express_no, $Order->express_co);
        } catch(\Exception $E) {
            $return_arr['has_express'] = 0;
            return $this->responseSuccessData($return_arr);
        }
        if (!isset($Express->result->list)) {
            $return_arr['has_express'] = 0;
            return $this->responseSuccessData($return_arr);
        } 
        // 计算物流详情
        $Expresslist = collect((array) $Express->result->list);
        preg_match("/【([^】|^【|.]+)】/", $Expresslist->last()->status, $res);
        $return_arr['express']['from']  = $res[1];
        $city_code = json_decode($Order->address_info)->city_code;
        $City = $ChinaArea->where('code', $city_code)->first();
        $return_arr['express']['to'] = $City->name;
        switch($Express->result->deliverystatus ) {
        case 0: // 已揽收
            $return_arr['express']['status'] = 0; // 标记已发货
           break;
        case 1: // 运输中 
            $return_arr['express']['status'] = 1; // 标记运输中
           break;
        case 2: // 派件中
            $return_arr['express']['status'] = 2; // 标记派件中
           break;
        case 3:
            $return_arr['express']['status'] = 3;// 标记已签收
           break;
        case 5: //疑难件
            $return_arr['express']['status'] = 3;// 标记已签收
           break;
        case 4: //派送失败
            $return_arr['express']['status'] = 3;// 标记已签收
           break;
        case 6: //退件签收
            $return_arr['express']['status'] = 3;// 标记已签收
           break;
        }
        $return_arr['express']['detail'] = $Express->result->list;
        return $this->responseSuccessData($return_arr);
    }

   /**
     * 详情详情
     *
     */
    public function show(Request $Request, Order $OrderModel, ChinaArea $ChinaArea)
    {
        $return_arr = [
            'address'   =>  [],
            'goods_info' => []
        ];
       (new CheckGoodsOrder())->scene('get_order_detail')->goCheck();
       $Order = $OrderModel->where('id', $Request->id)->first();

       $AddressArr = collect(json_decode($Order->address_info, true))->only(['name', 'address', 'city_code', 'phone']);
       $AddressArr['phone'] = substr_replace($AddressArr['phone'], '****',3,-4);
       $AddressArr['city_name'] = $ChinaArea->where('code', $AddressArr['city_code'])->first()->name;
       unset($AddressArr['city_code']);
       $return_arr['address'] = $AddressArr->toArray();
       $GoodsInfo = json_decode($Order->goods_info);
       $return_arr['goods_info']['out_trade_no'] = $Order->out_trade_no;
       $return_arr['goods_info']['title'] = $GoodsInfo->title;
       $return_arr['goods_info']['thumb'] = get_absolute_url($GoodsInfo->thumb);
       $return_arr['goods_info']['total_price'] = $Order->total_price;
       $return_arr['goods_info']['total_credit'] = $Order->total_credit;
       $return_arr['goods_info']['pay_type'] = $Order->pay_type;
       $return_arr['goods_info']['total'] = $Order->total;
       $return_arr['status'] = $Order->status;
       if (in_array($Order->status, [2, 3])) {
           $return_arr['goods_info']['express_type'] = $Order->express->name;
       } else {
           $return_arr['goods_info']['express_type'] = '订单未发货';
       }
           /* 状态 -1表示取消 0表示未支付 1表示已支付 2发货, 3表示已完成 */
       return $this->responseSuccessData($return_arr);
    }

    /**
     *  保存评价
     *
     */
    public function saveComment(Request $Request, GoodsComment $GoodsComment, Order $OrderModel)
    {
        (new CheckGoodsOrder())->scene('save_comment')->goCheck();
        $Order = $OrderModel->where('id', $Request->id)->first();
        $Order->status = 4;
        $Order->save();
        $GoodsInfo = json_decode($Order->goods_info);
        $GoodsComment->user_id       = $this->user()->id;
        $GoodsComment->goods_id      = $GoodsInfo->id;
        $GoodsComment->content       = $Request->content;
        $GoodsComment->stars         = $Request->goods_stars;
        $GoodsComment->img           = $Request->thumb;
        $GoodsComment->service_stars = $Request->service_stars;
        $GoodsComment->express_stars = $Request->express_stars;
        $GoodsComment->order_id = $Request->id;
        if ($GoodsComment->save()) {
            return $this->responseSuccess();
        } else {
            return $this->responseFail();
        }
    } 

    /**
     * 关闭订单
     */
    public function destroy(Request $Request, Order $OrderModel)
    {
        (new CheckGoodsOrder())->scene('delete')->goCheck();
        // :xxx  退款  退钱和退积分
        $Order =$OrderModel->where('id', $Request->id)->first();
        $Order->status =  -1;
        if($Order->save()) 
        {
            return $this->responseSuccess();
        } else {
            return $this->responseFail();
        }
    }

    /**
     * 申请退款
     *
     */
    public function refundSave(Request $Request, Order $OrderModel)
    {
        (new CheckGoodsOrder())->scene('refund')->goCheck();
        $Order = $OrderModel->where('id', $Request->id)->first();
        $Order->content = $Request->content;
        $Order->refund_status = 1;
        $Order->refund_thumb = $Request->thumb;
        if ($Order->save()) {
            return $this->responseSuccess();
        } else {
            return $this->responseFail();
        }
    }

    /**
     * 收货
     *
     */
    public function receive(Request $Request, Order $OrderModel)
    {
        $Order = $OrderModel->where('id', $Request->id)->first();
        if ($Order->status < 2 ) {
            throw new BaseException(['msg' => '商家未发货']);
        }
        $Order->status = 3;
        return $Order->save() ? $this->responseSuccess() : $this->responseFail();
    }

    /**
     * 订单重新支付
     *
     */
    public function repay(Request $Request, Order $OrderModel, PayService $PayService) 
    {
        $id = $Request->route()->id;
        $CheckResult = Validator::make(array_merge(['id' => $id], $Request->toArray()), [
            'id' => [
                'required',
                'exists:orders,id',
            ],
            'pay_type' => [
                'required',
                'in:wechat,alipay'
            ]
        ]);
        if ($CheckResult->fails()) {
            throw new BaseException([
                'msg' => $CheckResult->errors()->first()
            ]);
        }
        if (!$Order = $OrderModel->where('id', $id)->where('user_id', $this->user()->id)->first()) {
            throw new BaseException([
                'msg' => '没有这个订单'
            ]);
        }

        // 生成支付签名
        DB::beginTransaction();
        try {
            if ($Order->pay_type === 'wechat') {
                $app_pay_sign = $PayService->wechatPay([
                    'title'        => $Order->title,
                    'out_trade_no' => $Order->out_trade_no,
                    'total_price'  => $Order->total_price
                ]);
            } else {
                // :xxx 支付宝
            }
            $Order->app_pay_sign = json_encode($app_pay_sign);
            $Order->save();
            DB::commit();
        } catch(\Exception $E){
            DB::rollBack();
            return $this->responseFail('订单生成失败');
        }
        return $this->responseSuccessData($app_pay_sign);
    }
}
