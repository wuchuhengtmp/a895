<?php
/**
 * JWT获取验证
 *
 */
namespace App\Http\Validate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Api\Base as BaseException;
use App\Model\User as UserModel;

class CheckAuthorizations
{
    public function gocheck()
    {
        (new CheckPhone())->gocheck();
        if (!request()->has('password')) {
            throw new BaseException([
                'msg' => '请输入密码'
            ]);
        }
    }
}
