<?php

namespace App\Http\Service;

use App\Model\{
    User as UserModel,
    ReceivingAddress as ReceivingAddressModel
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

class MeUserInfo extends Base
{
    //  我的->个人主页

    /*
     *  获取用户信息
     *
     */
    public function getUserInfo(int $user_id)
    {
        $User = UserModel::select('avatar','nickname','id','credit')->where('id', $user_id)->first();
        $User->avatar = env('APP_URL').'/uploads/'.$User->avatar;
        return $User->toArray();
    }
    
    /*
     *  修改用户头像
     *
     */
    public function avatarUpdate(int $user_id){
        $Request = request();
        $User = UserModel::where('id', $user_id)->first();
        $User->avatar = $Request->avatar;
        if(!$User->save()){
            throw new SystemErrorException([
                'msg' => '修改用户头像失败'
            ]);
        }
    }

    /*
     *  修改用户昵称
     *
     */
    public function nickNameUpdate(int $user_id){
        $Request = request();
        $User = UserModel::where('id', $user_id)->first();
        $User->nickname = $Request->nickname;
        if(!$User->save()){
            throw new SystemErrorException([
                'msg' => '修改用户昵称失败'
            ]);
        }
    }

    /*
     *  获取收货地址列表
     *
     */
    public function receivingAddressList(int $user_id)
    {
        $info_list = ReceivingAddressModel::select('id','name','phone','address','check')
            ->where('user_id', $user_id)->orderBy('check','desc')
            ->orderBy('id','desc')->get()->toArray();
        for ($i=0;$i<count($info_list);$i++){
            $info_list[$i]['phone'] = substr_replace($info_list[$i]['phone'],'****',3,-4);;
        }
        return $info_list;
    }

    /*
     *  添加收货地址
     *
     */
    public function receivingAddressAdd(int $user_id){
        $Request = request();
        $bool = ReceivingAddressModel::where(['user_id'=>$user_id,'check'=>1])->first();
        if($bool){
            ReceivingAddressModel::where(['user_id'=>$user_id,'check'=>1])->update(['check'=>0]);
        }
        $ReceivingAddress = new ReceivingAddressModel();
        $ReceivingAddress->user_id = $user_id;
        $ReceivingAddress->name = $Request->name;
        $ReceivingAddress->phone = $Request->phone;
        $ReceivingAddress->city = $Request->city;
        $ReceivingAddress->address = $Request->address;
        if(!$ReceivingAddress->save()){
            throw new SystemErrorException([
                'msg' => '添加收获地址失败'
            ]);
        }
    }

    /*
     *  修改收货地址
     *
     */
    public function receivingAddressUpdate(int $user_id)
    {
        $Request = request();
        if($Request->check == 1){
            ReceivingAddressModel::where(['user_id'=>$user_id,'check'=>1])->update(['check'=>0]);
        }
        $ReceivingAddress = ReceivingAddressModel::where('id',$Request->id)->first();
        $ReceivingAddress->name = $Request->name;
        $ReceivingAddress->phone = $Request->phone;
        $ReceivingAddress->city = $Request->city;
        $ReceivingAddress->address = $Request->address;
        $ReceivingAddress->check = $Request->check;

        if(!$ReceivingAddress->save()){
            throw new SystemErrorException([
                'msg' => '修改收货地址失败'
            ]);
        }
    }

    /*
     *  删除收货地址
     *
     */
    public function receivingAddressDelete(int $id)
    {
        $Request = request();
        $ReceivingAddress = ReceivingAddressModel::where('id',$id)->first();

        if(!$ReceivingAddress->delete()){
            throw new SystemErrorException([
                'msg' => '删除收货地址失败'
            ]);
        }
    }
}
