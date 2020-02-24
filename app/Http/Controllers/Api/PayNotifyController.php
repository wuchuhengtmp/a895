<?php

namespace App\Http\Controllers\Api;

use App\Model\{
    User as UserModel,
    Order as OrderModel
};
use Illuminate\Support\Facades\DB;
use App\Http\Logic\{
    CreaditRecord as CreaditRecordLogic
};

class PayNotifyController extends \Yurun\PaySDK\Weixin\Notify\Pay
{
    /**
     * 后续执行操作
     * @return void
     */
    protected function __exec()
    {
        // 支付成功处理，一般做订单处理，$this->data 是从微信发送来的数据
        file_put_contents(__DIR__ . '/notify_result.txt', date('Y-m-d H:i:s') . ':' . var_export($this->data, true));

        $order = OrderModel::where('out_trade_no',$this->data['out_trade_no'])->first();

        if($order){
            DB::beginTransaction();
            //更新订单状态
            $bool = RechargeOrder::where('out_trade_no',$this->data['out_trade_no'])->update([
                'status'=>1,
                'updated_at'=>date("Y-m-d H:i:s"),
            ]);

            $user_bool = true;
            $creditbool = true;
            if($order['credit']!=0){
                $user_credit = UserModel::where('id',$order['user_id'])->first();
                $user_bool = UserModel::where('id',$order['user_id'])->update(['credit'=>$user_credit['credit'] - $order['credit']]);
                $creditbool = (new CreaditRecordLogic())->creaditRecordAdd($order['user_id'],'购买商品',$order['credit'],0);
            }

            file_put_contents(__DIR__ . '/notify_result.txt', $bool.'---'.$creditbool);
            if(!$bool || !$user_bool || !$creditbool){
                DB::rollBack();
                file_put_contents(__DIR__ . '/notify_result.txt', date('Y-m-d H:i:s') . ':订单更新失败' );
            }
            DB::commit();
            file_put_contents(__DIR__ . '/notify_result.txt', date('Y-m-d H:i:s') . ':订单更新成功' );
        }

        // 告诉微信我处理过了，不要再通过了
        $this->reply(true, 'OK');
    }
}

