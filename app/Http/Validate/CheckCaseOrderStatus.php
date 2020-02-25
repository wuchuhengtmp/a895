<?php
/**
 *  城市验证码必须存在
 *
 */
namespace App\Http\Validate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Api\Base as BaseException;
use App\Model\User as UserModel;

class CheckCityCode
{
    public function goCheck()
    {
        $all_route_params = request()->route()->parameters();
        $CheckResult = Validator::make($all_route_params, [
            'city_code' => 'required|exists:china_area,code'
        ], [
            'city_code.required' => '城市编码不能为空',
            'city_code.exists'  => '没有这个城市编码'
        ]);
        if ($CheckResult->fails()) {
            throw new BaseException([
                'msg' => $CheckResult->errors()->first()
            ]);
        }
    }
}
