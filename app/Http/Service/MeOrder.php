<?php

namespace App\Http\Service;

use App\Model\{
    User as UserModel,
    Goods as GoodsModel,
    Order as OrderModel,
    UserEvaluate as UserEvaluateModel
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

class MeOrder extends Base
{
    //  我的->我的订单

    /*
     *  获取订单列表
     *
     */
    public function getOrderList(int $user_id,int $type)
    {
        $list = OrderModel::select('id','out_trade_no','alipay_trade_no','goods_id','num','pay_type','credit','title')->where(['user_id'=>$user_id,'status'=>$type])->get()->toArray();
        if(!$list){
            $data[0] = '暂无该类型订单';
            return $data;
        }
        for($i=0;$i<count($list);$i++){
            $goods_info = GoodsModel::where('id',$list[$i]['goods_id'])->first();
            $list[$i]['thumb'] = env('APP_URL').'/uploads/'.$goods_info['thumb'];
            if($list[$i]['pay_type'] == 1){
                unset($list[$i]['alipay_trade_no']);
            }else{
                unset($list[$i]['out_trade_no']);
            }
        }

        return $list;
    }

    /*
     *  关闭或删除订单
     *
     */
    public function orderDelete(int $id)
    {
        $check = OrderModel::where(['id'=>$id])->first();
        if($check['status']==0){
            $bool = OrderModel::where(['id'=>$id])->update(['status'=>-1]);
            if(!$bool){
                throw new SystemErrorException([
                    'msg' => '关闭订单失败'
                ]);
            }
        }elseif ($check['status']==2){
            $bool = OrderModel::where(['id'=>$id])->delete();
            if(!$bool){
                throw new SystemErrorException([
                    'msg' => '删除订单失败'
                ]);
            }
        }else{
            throw new SystemErrorException([
                'msg' => '此订单不能执行该操作'
            ]);
        }
    }

    /*
     *  查看物流信息
     *
     */
    public function orderLogistics(int $id)
    {
        $logistics_number = OrderModel::where('id',$id)->first();
        if(!$logistics_number['logistics_number']){
            $data[0] = '暂无物流信息';
            return $data;
        }
        $status_url   = "https://api.m.sm.cn/rest?method=kuaidi.getdata&sc=express_cainiao&q=%E5%BF%AB%E9%80%92" . $logistics_number['logistics_number'] . "&callback=jsonp2";
        $status_url_json  = https_request($status_url);
        $status_url_json = jsonp_decode($status_url_json,true);
        $res = [
            'company'=>$status_url_json['data']['company'],
            'logistics_number'=>$logistics_number['logistics_number'],
            'messages'=>$status_url_json['data']['messages'],
            'status'=>$status_url_json['data']['status'],
        ];
        return $res;
    }

    /*
     *  确认收货
     *
     */
    public function orderConfirm(int $user_id,int $id)
    {
        $check = OrderModel::where('id',$id)->first();
        if($check['status'] == 1){
            $bool = OrderModel::where(['id'=>$id,'user_id'=>$user_id])->update(['status'=>2]);
            if(!$bool){
                throw new SystemErrorException([
                    'msg' => '确认收货失败'
                ]);
            }
        }else{
            throw new SystemErrorException([
                'msg' => '此订单不能执行该操作'
            ]);
        }
    }

    /*
     *  订单详情
     *
     */
    public function orderInfo(int $id)
    {
        $list = OrderModel::select('address_info','title','price','credit','pay_type','goods_id')->where('id',$id)->first();
        if(!$list){
            throw new SystemErrorException([
                'msg' => '该订单不存在'
            ]);
        }
        $img = GoodsModel::where('id',$list['goods_id'])->first();
        unset($list['goods_id']);
        $list['address_info'] = json_decode($list['address_info']);
        if($list['pay_type'] == 1){
            $list['pay_type'] = '微信支付';
        }else{
            $list['pay_type'] = '支付宝支付';
        }
        $list['img'] = env('APP_URL').'/uploads/'.$img['thumb'];
        $list['logistics_type'] = '普通快递';
        return $list->toArray();
    }

    /*
     *  评价商品
     *
     */
    public function evaluateGood(int $user_id)
    {
        $request = request();
        $check = GoodsModel::where('id',$request->goods_id)->first();
        if(!$check){
            throw new SystemErrorException([
                'msg' => '该商品不存在'
            ]);
        }

        $insert = [
            'user_id'=>$user_id,
            'goods_id'=>$request->goods_id,
            'star'=>$request->star,
            'content'=>$request->content,
            'img'=>$request->img,
            'created_at'=>date('Y-m-d H:i:s'),
            'updated_at'=>date('Y-m-d H:i:s')
        ];
        if(empty($request->star)){
            $insert['star'] = 0;
        }
        $bool = UserEvaluateModel::insert($insert);
        if(!$bool){
            throw new SystemErrorException([
                'msg' => '添加评价失败'
            ]);
        }
    }

}
