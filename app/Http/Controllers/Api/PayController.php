<?php

namespace App\Http\Controllers\Api;

use function Couchbase\defaultDecoder;
use Illuminate\Http\Request;
use App\Http\Validate\{
    CheckUserExists
};
use Illuminate\Support\Facades\Log;
use App\Http\Service\{
    PayOperation as PayOperationService
};
use \Overtrue\Wechat\Payment\Notify;
use App\Http\Service\Pay;
use App\Model\Order;
use App\Model\CaseOrder;

class PayController extends Controller
{
    /**
     * 商品支付
     *
     */
    public function payAdd($type)
    {
        (new CheckUserExists())->gocheck();
        $data = (new PayOperationService())->payAdd($type,$this->user()->id);
        return $this->responseSuccessData($data);
    }

    /**
     * 微信商品订单回调
     *
     */
    public function wxNotify(Request $Request)
    {
        $app = (new Pay())->getWechApp();
        $response = $app->handlePaidNotify(function ($message, $fail) use($Request){
            $Order = Order::where('out_trade_no', $message['out_trade_no'])->first();
            $Order->status = 1;
            $Order->save();
            Log::info($message);
            return true;
        });

        $response->send();
    }

    /**
     * 微信装修订单回调
     *
     */
    public function wxCaseOrderNotify(Request $Request)
    {
        $app = (new Pay())->getWechApp();
        $response = $app->handlePaidNotify(function ($message, $fail) use($Request){
            $CaseOrder = CaseOrder::where('out_trade_no', $message['out_trade_no'])->first();
            $CaseOrder->status = 100;
            $CaseOrder->save();
            Log::info($message);
            return true;
        });

        $response->send(); 
    }

    /**
     * 支付宝回调(APP版)
     *
     */
    public function aliPayNotify()
    {
        (new PayOperationService())->aliPayNotify();
        return $this->responseSuccess();
    }
}
