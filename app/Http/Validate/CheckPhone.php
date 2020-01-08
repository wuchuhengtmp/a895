<?php
/**
 * 手机号验证
 *
 */
namespace App\Http\Validate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Api\Base as BaseException;

class CheckPhone
{
    public function gocheck()
    {
        $CheckResult = Validator::make(request()->toArray(), [
            'phone' => [
                'required',
                'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/',
            ]
        ], [
            'phone.required' => '手机号不能为空',
            'phone.regex' =>'手机号不正确',
        ]);
        if ($CheckResult->fails()) {
            throw new BaseException([
                'msg' => $CheckResult->errors()->first()
            ]);
        }
    }
}
