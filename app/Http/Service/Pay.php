<?php

namespace App\Http\Service;

use EasyWeChat\Factory;
use App\Model\{
    User as UserModel,
    ReceivingAddress as ReceivingAddressModel
};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Exceptions\Api\{
    Base as BaseException,
    SystemErrorException
};
use App\Http\Logic\{
    Users  as UsersLogic,
    Credit as CreditLogic
};

class Pay extends Base
{
    /**
     * 微信支付
     *
     * @Order_info['title']   // 支付标题
     * @Order_info['out_trade_no']  // 支付商家单号
     * @Order_info['total_price'] // 支付额度
     * 
     * @return 应用支付签名
     */
    public function wechatPay($order_info) : array
    {
        $config = [
            // 必要配置
            'app_id'             => get_config('WX_APPID'),
                'mch_id'             => get_config('WX_MCH_ID'),
                'key'                => get_config('WX_PAY_KEY'),   // API 密钥
                // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
                'cert_path'          => 'path/to/your/cert.pem', // XXX: 绝对路径！！！！
                'key_path'           => 'path/to/your/key',      // XXX: 绝对路径！！！！
                'notify_url'         => 'http://a895.mxnt.net/',     // 你也可以在下单时单独设置来想覆盖它
            ];
        $app = Factory::payment($config);
        $app_pay_sign = $app->order->unify([
            'body'         => '商城商品-' . $order_info['title'],
            'out_trade_no' => $order_info['out_trade_no'],
            'total_fee'    => $order_info['total_price'] * 100,
            'trade_type'   => 'APP' // 请对应换成你的支付方式对应的值类型
        ]);
        if ($app_pay_sign['return_code'] === 'FAIL') {
            throw new BaseException([
                'msg' => "微信支付失败:{$app_pay_sign['return_msg']}"
            ]);
        }
        return $app_pay_sign;
    }
}

