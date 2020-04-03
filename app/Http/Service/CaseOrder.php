<?php
namespace App\Http\Service;

use EasyWeChat\Factory;
use App\Model\{
    CaseOrder as CaseOrderModel,
    PayTimes as PayTimesModel
};
use Illuminate\Support\Facades\DB;
use App\Model\Cases as CasesModel;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\Api\Base as BaseException;

class  CaseOrder extends Base
{
    public $times = [
            1 => '一期',
            2 => '二期',
            3 => '三期',
            4 => '四期',
            5 => '五期',
            6 => '六期',
            7 => '七期',
            8 => '八期',
            9 => '九期',
            10 => '十期',
            11 => '十一期',
            12 => '十二期',
            13 => '十三期',
            14 => '十四期',
        ];

    /**
     *   生成订单
     *
     */
    public function generateOrder($case_data)
    {
        $Case = (new CasesModel())->where('id', $case_data['case_id'])
            ->select([
            'designer_id',
            'clickes',
            'title',
            'apartment',
            'style',
            'area',
            'prepay',
            'thumb_url',
            'thumb_type',
            'thumb_video_url',
            'is_ecdemic_errand',
            'city_code',
            'min_price',
            'max_price',
            'is_to_build',
            'summary',
            'tags',
            'created_at',
            'updated_at',
            'community',
            'longitude',
            'latitude',
            'district_code',
            'province_code',
            'case_category_id',
            'is_commend',
            'period'
               ])
            ->first();
        $CaseOrderModel = new CaseOrderModel();
        $CaseOrderModel->case_id      = $case_data['case_id'];
        $CaseOrderModel->room         = $case_data['room'];
        $CaseOrderModel->area         = $case_data['area'];
        $CaseOrderModel->city_code    = $case_data['city_code'];
        $CaseOrderModel->phone        = $case_data['phone'];
        $CaseOrderModel->name         = $case_data['name'];
        $CaseOrderModel->pay_type     = $case_data['pay_type'];
        $CaseOrderModel->user_id      = $case_data['user_id'];
        $CaseOrderModel->out_trade_no = date('YmdHis', time()) . rand(0, 9999);
        $CaseOrderModel->case_info    = json_encode($Case->toArray());
        $CaseOrderModel->prepay_price = $Case->prepay;
        $CaseOrderModel->status       = 0;
        
        if ($case_data['pay_type'] === 'wechat') {
            $config = [
                // 必要配置
                'app_id'             => get_config('WX_APPID'),
                'mch_id'             => get_config('WX_MCH_ID'),
                'key'                => get_config('WX_PAY_KEY'),   // API 密钥

                // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
                'cert_path'          => 'path/to/your/cert.pem', // XXX: 绝对路径！！！！
                'key_path'           => 'path/to/your/key',      // XXX: 绝对路径！！！！
                'notify_url'         => env('APP_URL') . '/api/pays/wechat/case_order/natify',     // 你也可以在下单时单独设置来想覆盖它
            ];
            $app = Factory::payment($config);
            $result = $app->order->unify([
                'body' => '意向缴纳金-' . $Case->title,
                'out_trade_no' => $CaseOrderModel->out_trade_no,
                'total_fee' => $Case->designer->prepay_price * 100,
                'trade_type' => 'APP' // 请对应换成你的支付方式对应的值类型
            ]);
            $CaseOrderModel->prepay_id = $result['prepay_id']; 
            
         $result = $app->jssdk->appConfig($result['prepay_id']);
        } else if($case_data['pay_type'] === 'alipay') {
            // ... :xxx alipay
        }
        $CaseOrderModel->save();

        return $result;
    }

    /**
     * 订单详情
     *
     */
    public function getOrderById($id) : array
    {
        $CaseOrder = (new CaseOrderModel())->where('id', $id)
            ->first();
        
        $disk = Storage::disk('img');
        $Case = json_decode($CaseOrder->case_info);
        foreach($Case->period as $k=>$id) {
            $tmp[] = [
                'tite' => $this->times[$id],
                'id' => $id
            ];
        }
        $period = $tmp;
        $thumb_url = get_absolute_url($Case->thumb_url);
            return [
            'id'           => $CaseOrder->id,
            'title'        => $CaseOrder->title,
            'out_trade_no' => $CaseOrder->out_trade_no,
            'balance'      => $CaseOrder->balance,
            'times'        => $CaseOrder->times,
            'thumb_url'    => $thumb_url,
            'status'       => $this->getStatusById($CaseOrder->id),
            'reply'        => $CaseOrder->reply ?? '',
            'app_pay_type' => $CaseOrder->app_pay_type,
            'pay_account'  => get_config('PAY_ACCOUNT'),
            'Period'       => $period,
            'created_at'   => $CaseOrder->created_at->format("Y-m-d H:i:s"),
        ];

    }

    /**
     * 分期表
     *
     */
    public function getPayTimesById($order_id)
    {
        $PayTimes = DB::table('pay_times')->where('order_id', $order_id)
            ->select([
                "id",
                "total_price",
                "status",
                "pay_at",
                "reply",
                "images",
            ])
            ->get();
        // 付款进度条 
        $is_taget = 0;
        foreach($PayTimes as &$PayTime) {
            // 当前的付款
            $pay_at = strtotime($PayTime->pay_at);
            if (!$is_taget && $PayTime->status !== 100 ) {
                $PayTime->is_payprocess = 1;
                $is_taget = 1;
            } else {
                $PayTime->is_payprocess = 0;
            }
        }
        return $PayTimes->toArray();
        
    }

    /**
     * 登记全款支付申请
     */
    public function recordTotallPay($case_info)
    {
        $images = array_filter([$case_info['image1'], $case_info['image2']]);
        $images = json_encode($images);
        $CaseOrder = (new CaseOrderModel())->where('id', $case_info['id'])->first();
        $CaseOrder->status = 301;
        $pay_limit_at = $CaseOrder->created_at->timestamp + get_config('PAY_LIMIT_AT') * 60 * 60 * 24;
        if ($CaseOrder->payTimes->isEmpty()) {
            $PayTimes = new PayTimesModel();
        } else {
            $PayTimes = $CaseOrder->PayTimes->first();
        }
        $PayTimes->order_id = $CaseOrder->id;
        $PayTimes->status = 101;
        $PayTimes->total_price = $CaseOrder->balance;
        $PayTimes->images = $images;
        $PayTimes->pay_at = date("Y-m-d H:i:s", $pay_limit_at);
        $PayTimes->save();

        DB::beginTransaction();
        try{
            $CaseOrder->save();
            $PayTimes->save();
            DB::commit();
            return true;
        } catch(\Exception $E) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * 获取订单状态
     *
     */
    public function getStatusById($order_id)
    {
        $Order = (new CaseOrderModel())->where('id', $order_id)->first();
        if ($Order->app_pay_type == 'total' && $Order->status <= 200){
            return $Order->status;
        } else if($Order->status >= 500) { 
            return $Order->status;
        }else{
            if(!(new PayTimesModel())->where('order_id', $order_id)->count()) {
                return $Order->status;
            }
            $Paytime = (new PayTimesModel())->where('order_id', $order_id)
                ->where('status', '<>', 100)
                ->orderBy('id', 'asc')
                ->first();
            if (!$Paytime) {
                return 300;
            } else {
                switch($Paytime->status) {
                    case  101:
                    return 301;
                    case 102: return 302;
                    case  103: return 303;
                    default: return 200;
                }
            }
        }
    }
}
