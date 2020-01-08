<?php
/**
 * 手机号是否存在
 *
 */
namespace App\Http\Validate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Api\Base as BaseException;

class CheckPhoneIsExists
{
    public function gocheck()
    {
        $CheckResult = Validator::make(request()->toArray(), [
            'phone' => [
                'required',
                 'unique:users,phone'
            ]
        ], [
            'phone.required' => '手机号不能为空',
            'phone.unique' =>'手机号已存在',
        ]);
        if ($CheckResult->fails()) {
            throw new BaseException([
                'msg' => $CheckResult->errors()->first()
            ]);
        }
    }
}
