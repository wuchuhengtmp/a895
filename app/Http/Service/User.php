<?php

namespace App\Http\Service;

use App\Model\User as UserModel;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\Api\{
    Base as BaseException,
    SystemErrorException
};

class User extends Base
{
    /**
     * 新建用户
     *
     */
    public function registerStore(): void
    {
        $Request = request();
        $User = new UserModel();
        $User->password = bcrypt($Request->password);
        $phone_info = \Cache::get($Request->validate_key);  
        $User->phone = $phone_info['phone'];
        $avatar = get_config('DEFAULT_AVATOR');
        $path_info = pathinfo($avatar);
        $new_avatar = $path_info['dirname'] . '/' .  uniqid() . '.' . $path_info['extension'];
        Storage::disk('admin')->copy($avatar, $new_avatar);
        $User->avatar  = $new_avatar;
        $User->is_admin = 0; 
        $LastUser = UserModel::orderBy('id', 'desc')->first();
        $User->nickname = '手机用户_' . ++$LastUser->id;
        if (!$User->save()) {
            throw new SystemErrorException([
                'msg' => '用户创建失败'
            ]);
        }
    }

    /**
     *  重置密码
     *
     */
    public function resetPassword()
    {
        $Request = request();
        $phone_info = \Cache::get($Request->validate_key);
        $User = UserModel::where('phone', $phone_info['phone'])->first();
        $User->password = bcrypt($Request->password);
        if (!$User->save()) {
            if (!$User->save()) {
                throw new SystemErrorException([
                    'msg' => '重置密码失败'
                ]);
            }
        }
    }
}
