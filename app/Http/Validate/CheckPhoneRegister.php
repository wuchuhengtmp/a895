<?php
/**
 * 手机注册验证
 *
 */
namespace App\Http\Validate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Api\Base as BaseException;
use App\Model\User as UserModel;

class CheckPhoneRegister
{
    public function gocheck()
    {
        $CheckResult = Validator::make(request()->toArray(), [
            'validate_key' => [
                'required'
            ],
            'code' => [
                'required'
            ],
            'password' => [
                'required',
                /* 'digits_between:6,20' */
            ]
        ], [
            'validate_key.required' => '短信验证key不能为空',
            'code.required'         => '短信验证码不能为空',
            'password.required'     => '密码不能为空',
            'password.digits_between'     => '请输入6-20位的密码',
        ]);
        if ($CheckResult->fails()) {
            throw new BaseException([
                'msg' => $CheckResult->errors()->first()
            ]);
        }
        $cache_key = request()->validate_key;
        $has_key = \Cache::has($cache_key);
        if (!$has_key) {
            throw new BaseException([
                'msg' => '没有这个验证码，或验证码过期失效'
            ]); 
        } 
        $phone_info = \Cache::get($cache_key);
        if ($phone_info['type'] !== 'register') {
            throw new BaseException([
                'msg' => '请传入注册验证码的key'
            ]); 
        } 
        if ($phone_info['code'] !== request()->code) {
            throw new BaseException([
                'msg' => '验证码不正确'
            ]); 
        }
        $PhoneInfo = UserModel::where('phone', $phone_info['phone'])->limit(1)->get();
        if ($PhoneInfo->isNotEmpty()) {
            throw new BaseException([
                'msg' => '手机号已被注册'
            ]); 
        } 
    }
}
