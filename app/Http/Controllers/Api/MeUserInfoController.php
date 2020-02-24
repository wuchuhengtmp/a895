<?php

namespace App\Http\Controllers\Api;

use function Couchbase\defaultDecoder;
use Illuminate\Http\Request;
use App\Http\Validate\{
    CheckUserExists
};
use App\Http\Service\{
    MeUserInfo as MeUserInfoService
};

class MeUserInfoController extends Controller
{
    /**
     * 用户信息
     *
     */
    public function getUserInfo()
    {
        (new CheckUserExists())->gocheck();
        $userinfo = (new MeUserInfoService())->getUserInfo($this->user()->id);
        return $this->responseSuccessData($userinfo);
    }

    /**
     * 修改用户头像
     *
     */
    public function avatarUpdate(Request $Request){
        (new CheckUserExists())->gocheck();
        (new MeUserInfoService())->avatarUpdate($this->user()->id);
        return $this->responseSuccess();
    }
    
    /**
     * 修改用户昵称
     *
     */
    public function nickNameUpdate(Request $Request){
        (new CheckUserExists())->gocheck();
        (new MeUserInfoService())->nickNameUpdate($this->user()->id);
        return $this->responseSuccess();
    }

    /**
     * 获取收货地址列表
     *
     */
    public function receivingAddressList(Request $Request){
        (new CheckUserExists())->gocheck();
        $infoList = (new MeUserInfoService())->receivingAddressList($this->user()->id);
        return $this->responseSuccessData($infoList);
    }

    /**
     * 添加收货地址
     *
     */
    public function receivingAddressAdd(Request $Request){
        (new CheckUserExists())->gocheck();
        (new MeUserInfoService())->receivingAddressAdd($this->user()->id);
        return $this->responseSuccess();
    }

    /**
     * 修改收货地址
     *
     */
    public function receivingAddressUpdate(Request $Request){
        (new CheckUserExists())->gocheck();
        (new MeUserInfoService())->receivingAddressUpdate($this->user()->id);
        return $this->responseSuccess();
    }

    /**
     * 删除收货地址
     *
     */
    public function receivingAddressDelete($id){
        (new CheckUserExists())->gocheck();
        (new MeUserInfoService())->receivingAddressDelete($id);
        return $this->responseSuccess();
    }


}
