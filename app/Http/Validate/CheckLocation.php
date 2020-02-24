<?php
/**
 * 座标参数
 *
 */
namespace App\Http\Validate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Api\Base as BaseException;

class CheckLocation
{
    public function gocheck()
    {
        $CheckResult = Validator::make(request()->toArray(), [
            'location' => [
                'required'
            ]
        ], [
            'location.required' => '经纬座标不能为空'
        ]);
        if ($CheckResult->fails()) {
            throw new BaseException([
                'msg' => $CheckResult->errors()->first()
            ]);
        }
        
        $location = request()->location;
        if  (count(explode(',', $location)) !== 2) {
            throw new BaseException([
                'msg' => '座标参数location格式不正确'
            ]);
        }
        list($longitude, $latitude) = explode(',', $location);
        $CheckResult = Validator::make([
            'longitude' => $longitude,
            'latitude'  => $latitude
        ], [
            'longitude' => 'required|numeric|between:-180, 180',
            'latitude' => 'required|numeric|between:-90, 90',
            
        ], [
            'longitude.numeric' => '经度必须为数字',
            'longitude.between' => '经度取值范围必须为-180，180之间',
            'latitude.numeric' => '经度必须为数字',
            'latitude.between' => '纬度取值范围必须为-90，90之间',
        ]);
        if ($CheckResult->fails()) {
            throw new BaseException([
                'msg' => $CheckResult->errors()->first()
            ]);
        }
    }
}
