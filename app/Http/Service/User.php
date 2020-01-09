<?php

namespace App\Http\Service;

use App\Model\{
    User    as UserModel,
    SignLog as SignLogModel
};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Exceptions\Api\{
    Base as BaseException,
    SystemErrorException
};
use App\Http\Logic\{
    Users  as UsersLogic,
    Credit as CreditLogic
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

    /**
     * 用户签到列表
     *
     */
    public function getSignListByUserId(int $user_id)
    {
        $SingLog = SignLogModel::where('user_id', $user_id)
            ->orderBy('id', 'DESC')
            ->get();
        $credit_list = (new UsersLogic())->getCreditList();
        $credit_list = array_map(function($el) {
            $el['is_sign'] = 0;
            return $el;
        }, $credit_list);
        if ($SingLog->isNotEmpty()) {
            $sign_list = (new CreditLogic())->getSignDaysByUserId($user_id);
            foreach($credit_list as $key => &$credit) {
                $credit['is_sign'] = $key < count($sign_list) ? 1 : 0;
            }
        }
        // 当天签到
        $today_time = strtotime(date('Y-m-d'));
        $end_date   = date("Y-m-d H:i:s", ($today_time + 60 * 60 * 24 -1));
        $start_date = date("Y-m-d H:i:s", $today_time);
        $SingLog    = SignLogModel::whereBetween('created_at', [$start_date, $end_date])
            ->where('user_id', $user_id)
            ->limit(1)
            ->get();
        return [
            'list'    => $credit_list,
            'is_today_sign' => $SingLog->isEmpty() ? 0 : 1
        ];
        
    }

    /**
     * 用户签到
     *
     */
    public function signByUserId(int $user_id)
    {
        $today_time = strtotime(date('Y-m-d'));
        $end_date = date("Y-m-d H:i:s", ($today_time + 60 * 60 * 24 -1));
        $start_date = date("Y-m-d H:i:s", $today_time);
        $SingLog = SignLogModel::whereBetween('created_at', [$start_date, $end_date])
            ->where('user_id', $user_id)
            ->limit(1)
            ->get();
        if ($SingLog->isNotEmpty()) {
            throw new BaseException([
                'msg' => '您已经签到了'
            ]);
        }
        $SingLog = new SignLogModel();
        $SingLog->user_id = $user_id;
        DB::beginTransaction();
        try{
            $SingLog->save();
            // 登记签到积分
            (new CreditLog())->logSignCreditByUserId($user_id);
            DB::commit();
        } catch(\Exception $E) {
            DB::rollBack();
            throw new SystemErrorException([
                'msg' => '签到失败'
            ]);
        }
    }
}