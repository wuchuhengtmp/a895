<?php

namespace App\Http\Service;

use App\Model\{
    User as UserModel,
    Order as OrderModel
};
use http\Env\Request;
use Illuminate\Support\Facades\DB;
use Alipay\AlipayRequestFactory;
use Alipay\Key\AlipayKeyPair;
use Alipay\AopClient;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Log;
use App\Exceptions\Api\{
    Base as BaseException,
    SystemErrorException
};
use App\Http\Logic\{
    CreaditRecord as CreaditRecordLogic
};

class PayOperation extends Base
{
    // 商品支付
    public function payAdd(int $user_id)
    {
        $request = request();
        if(!preg_match("/^[1-9][0-9]*$/" ,$request->price)){
            throw new SystemErrorException([
                'msg' => '支付金额必须为整数！'
            ]);
        }
        $out_trade_no   = 'E'.date("YmdHis").rand(100000,999999);
        // 微信支付
        if($request->pay_type == 1){
            // 公共配置
            $params = new \Yurun\PaySDK\Weixin\Params\PublicParams;
            $params->appID  = env('APPID');
            $params->mch_id = env('MECHID');
            $params->key    = env('KEY');

            // SDK实例化，传入公共配置
            $pay = new \Yurun\PaySDK\Weixin\SDK($params);

            // 支付接口
            $request = new \Yurun\PaySDK\Weixin\APP\Params\Pay\Request;
            $request->body = '众富农场订单支付'; // 商品描述
            $request->out_trade_no = $out_trade_no; // 订单号
            $request->total_fee    = $request->price * 100; // 订单总金额，单位为：分
            $request->spbill_create_ip = env('IP'); // 客户端ip，必须传正确的用户ip，否则会报错
            $request->notify_url = env('APP_URL')."/api/wx_notify"; // 异步通知地址
            $request->scene_info->store_id = '门店唯一标识，选填';
            $request->scene_info->store_name = '众富农场';

            // 调用接口
            $result = $pay->execute($request);

            if($pay->checkResult())
            {
                $clientRequest = new \Yurun\PaySDK\Weixin\APP\Params\Client\Request;
                $clientRequest->prepayid = $result['prepay_id'];
                $pay->prepareExecute($clientRequest, $url, $data);
                $data['out_trade_no'] = $out_trade_no;
                $data['timestamp'] = strval($data['timestamp']);
                $address_info = [
                    'name'=>$request->name,
                    'phone'=>$request->phone,
                    'address'=>$request->address,
                ];
                $address_info = json_encode($address_info);

                $bool = OrderModel::insert([
                    'out_trade_no'=>$out_trade_no,
                    'user_id'=>$user_id,
                    'goods_id'=>$request->goods_id,
                    'num'=>$request->num,
                    'pay_type'=>$request->pay_type,
                    'address_info'=>$address_info,
                    'pay_at'=>time(),
                    'price'=>$request->price,
                    'credit'=>$request->credit,
                    'title'=>$request->title,
                    'created_at'=>date("Y-m-d H:i:s"),
                    'updated_at'=>date("Y-m-d H:i:s"),
                ]);

                if($bool){
                    $data = ['wechatdata'=>$data];
                    return $data;
                }else{
                    throw new SystemErrorException([
                        'msg' => '微信支付失败！'
                    ]);
                }
            }
            else
            {
                throw new SystemErrorException([
                    'msg' => $pay->getErrorCode() . ':' . $pay->getError()
                ]);
            }
        }
        // 支付宝支付
        elseif ($request->pay_type == 2){
            $order = [
                'out_trade_no' => $out_trade_no,
                'total_amount' => $request->price,
                'subject' => '充值'
            ];

            $alipay = Pay::alipay([
                'app_id' => '2021001105667414',
                'return_url' => '',
                'notify_url' =>  "http://a885.mxnt.net/api/alipay_notify" ,
                'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAyaBGup3W+Ygb8XhthfrO4Qj+tW39ddeq9JfhAnmK4TvoZoRTpCtIwDwfyIy4wsHCfflzHGzx4EgS27Poh4lG3b2DVQ3eZ6HodfLS0ulVg4eu7uKysE3TT9q1ltMvMhO8ANdEtbHVwQizU8tr62Cgprsc+e+1S6053tUO4QZeHOcfYZyI3gQPqIEhk7V/M+pyRTugMaXk5JbWOExA1KeXIlcmnHb+AnSNxGAyeKraTxmqlDsarW0Lyv1nWWSK2xeWIv5ertHtHfDaZDtwIOt7IZEnE31UaWIcRaLeNDHOz6lYGav2uwoKCvFEm+GTvZzV3SeR3xXMOkgal9WehfC94wIDAQAB',
                // 加密方式： **RSA2**
                'private_key' =>'MIIEpAIBAAKCAQEAyaBGup3W+Ygb8XhthfrO4Qj+tW39ddeq9JfhAnmK4TvoZoRTpCtIwDwfyIy4wsHCfflzHGzx4EgS27Poh4lG3b2DVQ3eZ6HodfLS0ulVg4eu7uKysE3TT9q1ltMvMhO8ANdEtbHVwQizU8tr62Cgprsc+e+1S6053tUO4QZeHOcfYZyI3gQPqIEhk7V/M+pyRTugMaXk5JbWOExA1KeXIlcmnHb+AnSNxGAyeKraTxmqlDsarW0Lyv1nWWSK2xeWIv5ertHtHfDaZDtwIOt7IZEnE31UaWIcRaLeNDHOz6lYGav2uwoKCvFEm+GTvZzV3SeR3xXMOkgal9WehfC94wIDAQABAoIBAFh5gk/lby30Mf7Vz4mZMyrAXbSTXUNWrefCtbP2TaDkPuitbF5/t97TA0dnqLOdfpD04zQ4AjNerRfHbGP9MyGeRYEPIubqvyzyrtxqE2IyKesdgzemDvHdkQ1sAivBSSA9ViM8tAWOodJFU0foENbZCLAMJcvdaaWEbJEF0EpxwDHv/TEJGEujF7bNeFhiu0oFKn/1gSBPTU0P4+3t2PJTl6O3sKZoBIZDDCmhkXcR3zJ+W2tm0K6q3zrhNkjfGeBOnjCcoTcqiZ2W6LleiiRISpwNoFtz6sEkbNUrlbIE5vjOiJKVxijDlCZ1qHNg85N0M0TOswYUkh/avbzl2+ECgYEA/yN7K3pQa7s6FiY8IHOoMweBFdBFYgQYLaxMQa1DrMsedMvUdNFL82A4LbtgXaTgbIaFrUCK8csNfI1PfBd24C3AH7agVVgDxAW9LvO4T0WR2Ugztbf+Eiuv68MpY/jjsvsqdY8wfoLMAUfEcfQVgx8XDLALwsZORF1wvrjvE0sCgYEAyk6LMq/dE1qdoUhcTd/VQWOBEMDE99TUWewcncTdysFc0ZU/YjNzCEFxXbv504YR8nkxoIrBe/8D7fNfBZgxLnV/NRhktX1J/4aZr/d+GATII9Pd6+FekmW6ok98pnebEUL9ekDkBlrkCi2fsk8LT8iNjKuPEWeEVI9GSoB4yMkCgYEA+FBoCCC7NJ68IeKEknD+OuwKzlgtrv+dKJaQgTtIqlvmAHaBCFDLQsta4eeEGp/lbLpgUAaJNFsTfS1rNrL/l5/vZO4xjd5ji9yqC5BYyY5ELN0AttOkC7tJNIR1PD94HTImWNRLtlVGh9h5cQ7GAR+5JzgPujmW4yKuIHGM/ZECgYAGg2zb3umhO+OjU68VGsXE6y02mt48lG+ZzY5GThZN9tfEL6fww3NKqsC5odmzQ7fENL6ySoVcNqOrv5Apn/LFaicEUJq9dSEyxuSf07oNj+nZrXKRq5nd4MSXgTOkMGmfrqZ2jyxIQBjjcwCXPxBAK1bTVpqulsSd7Fb5AxXgcQKBgQDnwctAecE7XIwfeuxc25sYDd7vL0S72YA9I3CYlem/CXylT8PEtyVRmPZgqb5Wf6drcnH3NTjShxJ844xlcaW/odfScs+MLKvvGVZuSbUet+VQjub4v5aZjrJRvH52/9NdEsLuhh1sZiWeM7n20c6ETcJndTKPPxj3ptYNCYSh3Q==',
                'log' => [ // optional
                    'file' => './logs/alipay.log',
                    'level' => 'debug', // 建议生产环境等级调整为 info，开发环境为 debug
                    'type' => 'single', // optional, 可选 daily.
                    'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
                ],
                'http' => [ // optional
                    'timeout' => 5.0,
                    'connect_timeout' => 5.0,
                    // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
                ],
                'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
            ])->app($order);
            
            $address_info = [
                'name'=>$request->name,
                'phone'=>$request->phone,
                'address'=>$request->address,
            ];
            $address_info = json_encode($address_info);
            $bool = OrderModel::insert([
                'user_id'=>$user_id,
                'goods_id'=>$request->goods_id,
                'num'=>$request->num,
                'pay_type'=>$request->pay_type,
                'address_info'=>$address_info,
                'pay_at'=>time(),
                'price'=>$request->price,
                'credit'=>$request->credit,
                'alipay_trade_no'=>$out_trade_no,
                'title'=>$request->title,
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s"),
            ]);

            if($bool){
                return $alipay->getContent();
            }else{
                throw new SystemErrorException([
                    'msg' => '支付宝支付失败！'
                ]);
            }



            /* // 公共配置  (网页版)
             $params = new \Yurun\PaySDK\Alipay\Params\PublicParams;
             $params->appID  = '2088731195620325';
             $params->md5Key  = '3xnm87hrz400yj3kvobp59iw8w1m7nik';

             // SDK实例化，传入公共配置
             $pay = new \Yurun\PaySDK\Alipay\SDK($params);

             $request = new \Yurun\PaySDK\Alipay\Params\WapPay\Request;
             $request->notify_url = env('APP_URL')."/api/alipay_notify"; // 支付后通知地址（作为支付成功回调，这个可靠）
             $request->return_url = ''; // 支付后跳转返回地址
             $request->businessParams->out_trade_no = $out_trade_no; // 商户订单号
             $request->businessParams->total_fee =$num; // 价格
             $request->businessParams->subject = '众富农场'; // 商品标题
             $request->businessParams->show_url = ''; // 用户付款中途退出返回商户网站的地址。

             $pay->prepareExecute($request, $url, $data);//prepareExecute


             $bool = RechargeOrder::insert([
                 'user_id'=>$user['id'],
                 'pay_price'=>$num,
                 'out_trade_no'=>$out_trade_no,
                 'created_at'=>date("Y-m-d H:i:s"),
                 'updated_at'=>date("Y-m-d H:i:s"),
             ]);

             if($bool){
                 return show_json('success', ['url'=>$url,'data'=>$data]);// 输出的是可以让app直接请求的url
             }else{
                 throw new SystemErrorException([
                    'msg' => '支付宝支付失败！'
                ]);
             }*/
        }
        throw new SystemErrorException([
            'msg' => '支付类型错误！'
        ]);
    }

    // 支付宝回调(APP版)
    public function aliPayNotify(){

        $alipay = Pay::alipay([
            'app_id' => '2021001105667414',
            'return_url' => '',
            'notify_url' =>  "http://a885.mxnt.net/api/alipay_notify" ,
            'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAq3yDbWd35wXhPn36+mQd/LUWOeGEAaIuDLtxY6GtmkIHTLLqSaYoTQMgA/mqQHV9ufBXzSoVHG5GBa7Y86Ylspar9DE4oboey5pshx6M2I6OT+matWZtOzYKRNZzE6zShSNYprfUDuWJXtBwEyklIhObi2gUW2BmimBqManD3NU3joXNta9fLb/CsfN0K9IVcQvLRfNZ/u9t2zhblIboQ/4daSQFOO0C5TKNw6N42yr3hOuzi5MWRiFlWOdXTv8IGGHqFcviKcz3fKXfzgHqFmryTZ7R2H/eI42G/E+GklxrmaTtEAZKcpug5uVNgV8wsmXv4j8uWgQfWFHDA47GLwIDAQAB',
            // 加密方式： **RSA2**
            'private_key' =>'MIIEpAIBAAKCAQEAyaBGup3W+Ygb8XhthfrO4Qj+tW39ddeq9JfhAnmK4TvoZoRTpCtIwDwfyIy4wsHCfflzHGzx4EgS27Poh4lG3b2DVQ3eZ6HodfLS0ulVg4eu7uKysE3TT9q1ltMvMhO8ANdEtbHVwQizU8tr62Cgprsc+e+1S6053tUO4QZeHOcfYZyI3gQPqIEhk7V/M+pyRTugMaXk5JbWOExA1KeXIlcmnHb+AnSNxGAyeKraTxmqlDsarW0Lyv1nWWSK2xeWIv5ertHtHfDaZDtwIOt7IZEnE31UaWIcRaLeNDHOz6lYGav2uwoKCvFEm+GTvZzV3SeR3xXMOkgal9WehfC94wIDAQABAoIBAFh5gk/lby30Mf7Vz4mZMyrAXbSTXUNWrefCtbP2TaDkPuitbF5/t97TA0dnqLOdfpD04zQ4AjNerRfHbGP9MyGeRYEPIubqvyzyrtxqE2IyKesdgzemDvHdkQ1sAivBSSA9ViM8tAWOodJFU0foENbZCLAMJcvdaaWEbJEF0EpxwDHv/TEJGEujF7bNeFhiu0oFKn/1gSBPTU0P4+3t2PJTl6O3sKZoBIZDDCmhkXcR3zJ+W2tm0K6q3zrhNkjfGeBOnjCcoTcqiZ2W6LleiiRISpwNoFtz6sEkbNUrlbIE5vjOiJKVxijDlCZ1qHNg85N0M0TOswYUkh/avbzl2+ECgYEA/yN7K3pQa7s6FiY8IHOoMweBFdBFYgQYLaxMQa1DrMsedMvUdNFL82A4LbtgXaTgbIaFrUCK8csNfI1PfBd24C3AH7agVVgDxAW9LvO4T0WR2Ugztbf+Eiuv68MpY/jjsvsqdY8wfoLMAUfEcfQVgx8XDLALwsZORF1wvrjvE0sCgYEAyk6LMq/dE1qdoUhcTd/VQWOBEMDE99TUWewcncTdysFc0ZU/YjNzCEFxXbv504YR8nkxoIrBe/8D7fNfBZgxLnV/NRhktX1J/4aZr/d+GATII9Pd6+FekmW6ok98pnebEUL9ekDkBlrkCi2fsk8LT8iNjKuPEWeEVI9GSoB4yMkCgYEA+FBoCCC7NJ68IeKEknD+OuwKzlgtrv+dKJaQgTtIqlvmAHaBCFDLQsta4eeEGp/lbLpgUAaJNFsTfS1rNrL/l5/vZO4xjd5ji9yqC5BYyY5ELN0AttOkC7tJNIR1PD94HTImWNRLtlVGh9h5cQ7GAR+5JzgPujmW4yKuIHGM/ZECgYAGg2zb3umhO+OjU68VGsXE6y02mt48lG+ZzY5GThZN9tfEL6fww3NKqsC5odmzQ7fENL6ySoVcNqOrv5Apn/LFaicEUJq9dSEyxuSf07oNj+nZrXKRq5nd4MSXgTOkMGmfrqZ2jyxIQBjjcwCXPxBAK1bTVpqulsSd7Fb5AxXgcQKBgQDnwctAecE7XIwfeuxc25sYDd7vL0S72YA9I3CYlem/CXylT8PEtyVRmPZgqb5Wf6drcnH3NTjShxJ844xlcaW/odfScs+MLKvvGVZuSbUet+VQjub4v5aZjrJRvH52/9NdEsLuhh1sZiWeM7n20c6ETcJndTKPPxj3ptYNCYSh3Q==',
            'log' => [ // optional
                'file' => './logs/alipay.log',
                'level' => 'debug', // 建议生产环境等级调整为 info，开发环境为 debug
                'type' => 'single', // optional, 可选 daily.
                'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
            ],
            'http' => [ // optional
                'timeout' => 5.0,
                'connect_timeout' => 5.0,
                // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
            ],
            'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
        ]);

        $data = $alipay->verify(); // 是的，验签就这么简单！
        $data = $data->toArray();

        if (in_array($data['trade_status'],['TRADE_SUCCESS','TRADE_FINISHED']))
        {
            $out_trade_no = $data['out_trade_no'];

            $order = OrderModel::where('alipay_trade_no',$out_trade_no)->first();

            if($order){
                DB::beginTransaction();
                //更新订单状态
                $bool = OrderModel::where('alipay_trade_no',$out_trade_no)->update([
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

                if(!$bool || !$user_bool || !$creditbool){
                    DB::rollBack();
                }
                DB::commit();
            }

        } else {
            file_put_contents(__DIR__ . '/alipaynotify_result.txt', '验证失败');
        }

    }
}
