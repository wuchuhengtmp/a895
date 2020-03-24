<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Model\{
    Calculates,
    ChinaArea,
    Tmp
};
use Illuminate\Support\Facades\DB;
use Overtrue\Pinyin\Pinyin;

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
        
        // 基本价格
        if ($city_addtion = $Calculates->where('key', $Request->input('city_code'))->first()) {
            $total_price = round($total_price * $city_addtion->value / 100, 2);
        }
        $Vipes = Calculates::where('type', 'vip')->select(['key', 'value'])->get();
        foreach($Vipes as $Vip) {
            $min_price = $total_price * $Vip->value;
            $max_price = $total_price * $Vip->value * (1 + get_calculate('valuation') / 100 );
            // 价格
            $tmp['min_price'] = number_format($min_price / 10000, 1);
            $tmp['max_price'] = number_format($max_price / 10000, 1);
            // 人工费
            $tmp['min_labor_price'] = number_format($min_price * get_calculate('labor') / 100 / 10000, 1);
            $tmp['max_labor_price'] = number_format($max_price * get_calculate('labor') / 100 / 10000, 1);
            // 材料费 
            $tmp['min_material_price'] = number_format($min_price * get_calculate('material') / 100 / 10000, 1);
            $tmp['max_material_price'] = number_format($max_price * get_calculate('material') / 100 / 10000, 1);
            // 详情列表
            $scale = [];
            $Request->input('room')    && $scale['room'] = get_calculate('room') * $Request->input('room');
            $Request->input('kitchen') && $scale['kitchen'] = get_calculate('kitchen') * $Request->input('kitchen');
            $Request->input('parlour') && $scale['parlour'] = get_calculate('parlour') * $Request->input('parlour');
            $Request->input('toilet')  && $scale['toilet'] = get_calculate('toilet') * $Request->input('toilet');
            $total = array_sum($scale);
            $cn_name = [
                'room'    => '房间',
                'kitchen' => '厨房',
                'parlour' => '阳台',
                'toilet'  => '卫生间'
            ];
            $tmp['list'] = [];
            foreach($scale as $item => $value) {
                $tmp2 = [];
                $tmp2['name'] = $cn_name[$item];
                $tmp2['price'] = number_format($value / $total * $max_price, 2);
                $tmp2['scale'] = round($value/$total, 2) * 100 . '%';
                $tmp['list'][] = $tmp2;
            }
            $data[] = $tmp;
        }
        $packes = ['简装', '精装', '豪华服装'];
        foreach($data as $k => &$el) {
            $el['DecorationType'] = $packes[$k];
        }
        return $this->responseSuccessData($data);
    }
    
}
