<?php

namespace App\Http\Controllers\Api;

use function Couchbase\defaultDecoder;
use Illuminate\Http\Request;
use App\Http\Validate\{
    CheckUserExists
};
use App\Http\Service\{
    PayOperation as PayOperationService
};

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
     * 微信回调
     *
     */
    public function wxNotify()
    {
        // 公共配置
        $params = new \Yurun\PaySDK\Weixin\Params\PublicParams;
        $params->appID  = env('APPID');
        $params->mch_id = env('MECHID');
        $params->key    = env('KEY');

        // SDK实例化，传入公共配置
        $sdk = new \Yurun\PaySDK\Weixin\SDK($params);

        $payNotify = new PayNotifyController;
        try{
            $sdk->notify($payNotify);
        }catch(Exception $e){
            file_put_contents(__DIR__ . '/notify_result.txt', $e->getMessage() . ':' . var_export($payNotify->data, true));
        }
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
