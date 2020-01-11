<?php
/**
 * 验证用户是否存在
 *
 */
namespace App\Http\Validate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Api\{
    Base as BaseException,
    SystemErrorException
};
use App\Model\User as UserModel;

class CheckUserExists
{
    public function gocheck()
    {
        $Request = request();
        $token = str_replace('Bearer ', '', $Request->header('authorization'));
        $token = explode('.', $token);
        $token = $token[1];
        $token = base64_decode($token);
        $UserInfo = json_decode($token);
        $user_id = $UserInfo->sub;
        $User = UserModel::where('id', $user_id)->limit(1)->get();
        if ($User->isEmpty()) {
            throw new BaseException([
                'msg' =>  '没有这个用户'
            ]);
        }
        
    }
}
