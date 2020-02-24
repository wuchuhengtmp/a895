<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Model\Calculates;

class CalculateController extends Controller
{
    public function index(Request $Request, Calculates $Calculates)
    {
        $validatedData = $Request->validate([
            "room"      => "required|numeric",
            "kitchen"   => "required|numeric",
            "parlour"   => "required|numeric",
            "toilet"    => "required|numeric",
            "area"      => "required|numeric",
            "city_code" =>" required|numeric|exists:china_area,code"
        ], [
            'city_code.exists' => '没有这个城市'
        ]);
        // 计算价格 
        $is_more_room = $Request->input('room') > 0 ? 1 : 0;
        $is_more_room += $Request->input('kitchen') > 0 ? 1 : 0;
        $is_more_room += $Request->input('parlour') > 0 ? 1 : 0;
        $is_more_room += $Request->input('toilet') > 0 ? 1 : 0;
        foreach($Request->toArray() as $k=>$v) {
            if (in_array($k, ['room', 'kitchen', 'parlour', 'toilet']) && $v > 0) {
            // 一种类型按一种算
                if ($is_more_room === 1) {
                    $total_price = round($Request->area * $Calculates->getPriceBykey($k));
                    if ($city_addtion = $Calculates->where('key', $Request->input('city_code'))->first()) {
                       $total_price = round($total_price * $city_addtion->value / 100, 2);
                    }
                    return $this->responseSuccessData([ 'total_price' => $total_price ]);
                } else {
                    // 多种类型的房间按均价算
                    static $count_type, $total_price;
                    $count_type++;
                    $total_price += $Calculates->getPriceBykey($k);
                }
            }
        }
        $total_price = round($total_price / $count_type * $Request->input('area'), 2); 

        if ($city_addtion = $Calculates->where('key', $Request->input('city_code'))->first()) {
            $total_price = round($total_price * $city_addtion->value / 100, 2);
        }
        return $this->responseSuccessData([ 'total_price' => $total_price ]);
    }
    
}
