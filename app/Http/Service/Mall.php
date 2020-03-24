<?php

namespace App\Http\Service;

use App\Model\{
    User         as UserModel,
    Goods        as GoodsModel,
    UserEvaluate as UserEvaluateModel,
    GoodsComment as GoodsCommentModel,
    Order        as OrderModel,
    Address as AddressModel
};
use Illuminate\Support\Facades\DB;
use App\Exceptions\Api\{
    Base as BaseException,
    SystemErrorException
};
use App\Http\Logic\{
    Evaluate      as EvaluateLogic,
    CreaditRecord as CreaditRecordLogic
};
use Illuminate\Support\Facades\Storage;

class Mall extends Base
{
    /*
     *  获取商品列表
     *
     */
    public function getGoodsList()
    {
        $List= GoodsModel::select('id','title','credit','thumb')->where('status',1)->orderBy('id','desc')->get()->toArray();
        if(!$List){
            throw new SystemErrorException([
                'msg' => '暂无上架商品'
            ]);
        }
        for($i=0;$i<count($List);$i++){
            $List[$i]['thumb'] = env('APP_URL').'/uploads/'.$List[$i]['thumb'];
        }
        return $List;
    }

    /*
     *  获取商品详情信息
     *
     */
    public function getGoodsInfo(int $id)
    {
        $info= GoodsModel::select('id', 'status', 'title','tags','content','credit','price','thumb')->where('id',$id)->first();
        if(!$info){
            throw new SystemErrorException([
                'msg' => '该商品不存在'
            ]);
        }
        $info['thumb'] = env('APP_URL').'/uploads/'.$info['thumb'];
        /* $userEvaluate = UserEvaluateModel::select('user_id','star','content as evaluate_content','img','created_at as time') */
        /*     ->where('goods_id',$id)->get()->toArray(); */
        /* if(!$userEvaluate){ */
        /*     $userEvaluate = '暂无评论'; */
        /* }else{ */
        /*     $userEvaluate = (new EvaluateLogic())->getEvaluate($userEvaluate); */
        /* } */
        /* $info['userEvaluate'] = $userEvaluate; */
        return $info->toArray();
    }
    
    /**
     * 获取商品评价
     *
     */
    public function getCommentsById(int $good_id)
    {
        $return_data = [
            'list' => [],
            'total' => 0
        ];
        $Comments = (new GoodsCommentModel())->where('goods_id', $good_id)->paginate(10);
        if ($Comments->isNotEmpty()) {
            foreach($Comments as $Comment) {
                $tmp = [];
                $tmp['id'] = $Comment->id;
                $tmp['nickname'] = $Comment->user->nickname;
                $tmp['avatar'] = Storage::disk('img')->url($Comment->user->avatar);
                if (is_json($Comment->img)) {
                    $tmp['img'] = json_decode($Comment->img, true);
                } else  {
                    $tmp['img'] = [$Comment->img];
                }
                $tmp['stars'] = $Comment->stars;
                $tmp['created_at'] = format_time($Comment->created_at->timestamp);
                $return_data['list'][] = $tmp;
            }
            $return_data['total'] = $Comments->total();
            $return_data['lastpage'] = $Comments->lastPage();
        }
        return $return_data;
    }

    /**
     * 生成订单
     */
    public function generateOrder($order_info)
    {
        $Order = new OrderModel();
        $User = (new UserModel())->where('id', $order_info['user_id'])->first();
        $Order->out_trade_no = date('YmdHis', time()) . rand(0, 9999);
        $Order->user_id =$User->id;
        $Order->goods_id = $order_info['goods_id'];
        $Order->total = $order_info['total'];
        $Order->pay_type = $order_info['pay_type'];
        $Addresses = Db::table('address')->where('id', $order_info['address_id'])
            ->first();
        $address_info = (array)$Addresses;
        unset($address_info['created_at'], $address_info['updated_at']);
        $address_info = json_encode($address_info);
        $Order->address_info = $address_info;
        $Order->status = 0;
        $Goods = (new GoodsModel())->where('id', $order_info['goods_id'])
            ->first();
        $total_price = number_format(round($Goods->price * $order_info['total'], 2), 2);
        $Order->total_price = $total_price;
        $total_credit = $Goods->credit * $order_info['total'];
        $Order->total_credit = $total_credit;
        $Order->title = $Goods->title;
        unset($Goods->content);
        $Order->goods_info = json_encode($Goods->toArray());
        if ($Order->save()) {
            $User->credit -= $Order->total_credit;
            $User->save();
            return $Order; 
        } else {
            throw new BaseException([
                'msg' => '订单生成失败'
            ]);
        }
    }
}
