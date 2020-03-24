<?php

namespace App\Http\Controllers\Api;

use function Couchbase\defaultDecoder;
use Illuminate\Http\Request;
use App\Http\Validate\{
    CheckUserExists
};
use App\Http\Service\{
    Mall as MallService,
    Pay as PayService
};
use App\Model\{
    Slide as SlideModel,
    Subject as  SubjectModel
};
use Illuminate\Support\Facades\Storage;
use App\Http\Validate\{
    CheckGoods
};
use Illuminate\Support\Facades\DB;

class MallController extends Controller
{
    /**
     * 获取商品列表
     *
     */
    public function getGoodsList(SlideModel $SlideModel, SubjectModel $SubjectModel)
    {
        (new CheckUserExists())->gocheck();
        $return_data = [
            'list' => []
        ];
        $Subjects = $SubjectModel
            ->get();
        foreach($Subjects as &$Subject) {
            $tmp['title'] = $Subject->subject;
            $tmp['goods'] = [];
            if (isset($Subject->goods)) {
                foreach($Subject->goods as $Goods) {
                    $sub_tmp = [];
                    $sub_tmp['id'] = $Goods->id;
                    $sub_tmp['name'] = $Goods->title;
                    $sub_tmp['img'] = get_absolute_url($Goods->thumb);
                    $sub_tmp['integral'] = $Goods->credit;
                    $tmp['goods'][] = $sub_tmp;
                }
            }
                $tmp['bannerimg']  = get_absolute_url($Subject->thumb);
                $tmp['bannerurl'] = $Subject->redirection;
            $return_data['list'][] = $tmp;
        }

        return $this->responseSuccessData($return_data);
    }

    /**
     * 获取商品详情信息
     *
     */
    public function show($id)
    {
        (new CheckUserExists())->gocheck();
        $goodsInfo = (new MallService())->getGoodsInfo($id);
        return $this->responseSuccessData($goodsInfo);
    }

    /**
     * 幻灯片
     *
     */
    public function getAd(SlideModel $SlideModel)
    {
        $Slide = $SlideModel->where('id', 3)->first();
        return $this->responseSuccessData([
            'path' => Storage::disk('img')->url($Slide->url),
            'url' => $Slide->url
        ]);
    }

    /**
     * 获取商品评论
     */
    public function showComments(Request $Request, MallService $MallService)
    {
        (new CheckGoods())->scene('get_comments')->gocheck();
        $page_data = $MallService->getCommentsById($Request->id);
        return $this->responseSuccessData($page_data);
    }

    /**
     * 下单
     */
    public function addOrder(Request $Request, MallService $MallService, PayService $PayService)
    {
        (new CheckGoods())->scene('add_order')->gocheck();
        DB::beginTransaction();
        try {
            $Order = $MallService->generateOrder([
                'goods_id'   => $Request->id,
                'total'      => $Request->total,
                'pay_type'   => $Request->pay_type,
                'address_id' => $Request->address_id,
                'user_id'    => $this->user()->id
            ]);
            if ($Order->pay_type === 'wechat') {
                $app_pay_sign = $PayService->wechatPay([
                    'title'        => $Order->title,
                    'out_trade_no' => $Order->out_trade_no,
                    'total_price'  => $Order->total_price
                ]);
            } else {
                // :xxx 支付宝
            }
            $Order->app_pay_sign = json_encode($app_pay_sign);
            $Order->save();
            DB::commit();
        } catch(\Exception $E){
            DB::rollBack();
            return $this->responseFail('订单生成失败');
        }
        return $this->responseSuccessData($app_pay_sign);
    }

    /**
     * 订单列表
     *
     */
    public function ordersIndex(Request $Request)
    {
        (new CheckGoods())->scene('get_orders')->gocheck();
    }
}
