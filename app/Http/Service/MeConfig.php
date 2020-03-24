<?php

namespace App\Http\Service;

use App\Model\{
    User as UserModel,
    MeConfig as MeConfigModel,
    UserFeedback as UserFeedbackModel
};
use Illuminate\Support\Facades\DB;
use App\Exceptions\Api\{
    Base as BaseException,
    SystemErrorException
};
use App\Http\Logic\{
    Users  as UsersLogic,
    Credit as CreditLogic
};

class MeConfig extends Base
{
    //  我的->配置相关

    /*
     *  修改登录密码
     *
     */
    public function userPwdUpdate()
    {
        $Request = request();
        $phone_info = \Cache::get($Request->validate_key);
        $User = UserModel::where('phone', $phone_info['phone'])->first();
        $User->password = bcrypt($Request->password);
        if(!$User->save()){
            throw new SystemErrorException([
                'msg' => '修改登录密码失败'
            ]);
        }
    }

    /*
     *  转账密码设置
     *
     */
    public function transferPwd(int $user_id)
    {
        $Request = request();
        $User = UserModel::where('id', $user_id)->first();
        $User->transfer_pwd = bcrypt($Request->transferPwd);
        if(!$User->save()){
            throw new SystemErrorException([
                'msg' => '转账密码设置失败'
            ]);
        }
    }

    /*
     *  新增意见反馈
     *
     */
    public function FeedbackUpdate()
    {
        $Request = request();
        $UserFeedback = new UserFeedbackModel();
        $UserFeedback->content = $Request->content;
        $UserFeedback->contact = $Request->contact;
        if(!$UserFeedback->save()){
            throw new SystemErrorException([
                'msg' => '新增意见反馈失败'
            ]);
        }
    }

    /*
     *  获取关于我们的分类列表
     *
     */
    public function getTypeList()
    {
        $info_list = MeConfigModel::select('type','title')->get();
        return $info_list->toArray();
    }

    /*
     *  获取关于我们的分类信息
     *
     */
    public function getInfoByType($type)
    {
        $info_list = MeConfigModel::select('content')->where('type', $type)->first();
        if(!$info_list){
            throw new SystemErrorException([
                'msg' => '该分类不存在'
            ]);
        }
        return $info_list->toArray();
    }

}
