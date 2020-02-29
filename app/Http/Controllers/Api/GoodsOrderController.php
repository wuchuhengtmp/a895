<?php
/**
 * 商品订单
 *
 */
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Validate\{
    CheckGoodsOrder
};
use App\Model\{
    Order
};

use Illuminate\Support\Facades\Storage;

class GoodsOrderController extends Controller
{
   public function index(Order $OrderModel, Request $Request)
   {
       (new CheckGoodsOrder())->scene('get_order_list')->goCheck();
       $return_arr = ['list' => [], 'total' => 0];
       $Orders = $OrderModel->whereIn('status', explode(',', $Request->status))->paginate();
       foreach($Orders as $Order) {
           $order_info = [];
           $order_info = $Order->only('id', 'title', 'total_price', 'total_credit', 'status', 'goods_info');
           $goods_info = json_decode($order_info['goods_info'], true);
           $thumb = Storage::disk('img')->url($goods_info['thumb']);
           unset($order_info['goods_info']);
           $order_info['thumb'] = $thumb;
           $return_arr['list'][] = $order_info;
       }
       $return_arr['total'] = $Orders->total();
       return $this->responseSuccessData($return_arr);
   } 
}
