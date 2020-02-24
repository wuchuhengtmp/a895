<?php

namespace App\Http\Controllers\Api;

use function Couchbase\defaultDecoder;
use Illuminate\Http\Request;
use App\Http\Validate\{
    CheckUserExists
};
use App\Http\Service\{
    Mall as MallService
};

class MallController extends Controller
{
    /**
     * 获取商品列表
     *
     */
    public function getGoodsList()
    {
        (new CheckUserExists())->gocheck();
        $goodsList = (new MallService())->getGoodsList();
        return $this->responseSuccessData($goodsList);
    }

    /**
     * 获取商品详情信息
     *
     */
    public function getGoodsInfo($id)
    {
        (new CheckUserExists())->gocheck();
        $goodsInfo = (new MallService())->getGoodsInfo($id);
        return $this->responseSuccessData($goodsInfo);
    }

}
