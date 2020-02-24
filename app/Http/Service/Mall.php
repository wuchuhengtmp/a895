<?php

namespace App\Http\Service;

use App\Model\{
    User as UserModel,
    Goods as GoodsModel,
    UserEvaluate as UserEvaluateModel
};
use Illuminate\Support\Facades\DB;
use App\Exceptions\Api\{
    Base as BaseException,
    SystemErrorException
};
use App\Http\Logic\{
    Evaluate as EvaluateLogic,
    CreaditRecord as CreaditRecordLogic
};

class Mall extends Base
{
    //  商城

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
        $info= GoodsModel::select('title','tags','content','credit','price','thumb')->where('id',$id)->first();
        if(!$info){
            throw new SystemErrorException([
                'msg' => '该商品不存在'
            ]);
        }
        $info['thumb'] = env('APP_URL').'/uploads/'.$info['thumb'];
        $userEvaluate = UserEvaluateModel::select('user_id','star','content as evaluate_content','img','created_at as time')
            ->where('goods_id',$id)->get()->toArray();
        if(!$userEvaluate){
            $userEvaluate = '暂无评论';
        }else{
            $userEvaluate = (new EvaluateLogic())->getEvaluate($userEvaluate);
        }
        $info['userEvaluate'] = $userEvaluate;
        return $info->toArray();
    }

}
