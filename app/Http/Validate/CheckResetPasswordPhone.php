<?php
/**
 * 重置密码验证
 *
 */
namespace App\Http\Validate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Api\Base as BaseException;
use App\Model\User as UserModel;

class CheckResetPasswordPhone
{
    public function gocheck()
    {
        (new CheckPhone())->gocheck();
        $phone= request()->phone;
        $HasPhone = UserModel::where('phone', $phone)->limit(1)->get();
    }
}
