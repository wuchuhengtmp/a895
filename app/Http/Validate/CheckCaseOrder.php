<?php
/**
 *  项目提交订单验证
 *
 */
namespace App\Http\Validate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Api\Base as BaseException;
use App\Model\User as UserModel;

class CheckCaseOrder
{
    public function gocheck()
    {
        $CheckResult = Validator::make(
            request()->toArray(),
            [
                'case_id'  => [
                    'required',
                    'exists:cases,id'
                ],
                'area' => [
                    'required',
                    'int',
                    'gt:0'
                ],
                'room' => [
                    'required',
                ],
                'city_code' => [
                    'required',
                    'exists:china_area,code'
                ],
                'phone' => [
                    'required',
                    'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/',
                ],
                'name' => [
                    'required'
                ],
                'pay_type' => [
                    'required',
                    'in:wechat,alipay'
                ]
            ],
            [
                'case_id.required'   => '项目id不能为空',
                'case_id.exists'     => '项目id不存在',
                'area.required'      => '面积不能为空',
                'room.required'      => '户型不能为空',
                'city_code.required' => '城市不能为空',
                'city_code.exists'   => '没有这个城市',
                'phone.required'     => '手机不能为空',
                'phone.regex'        => '手机格式不正确',
                'name.required'      => '用户名不能为空',
                'pay_type.required' => '支付方式不能为空',
                'pay_type.in' => '支付方式请选择wechat 或 alipay'
            ]
        );
        if($CheckResult->fails()) {
            throw new BaseException([
                'msg' => $CheckResult->errors()->first()
            ]);
        }
    }
}
