<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Validate\{
    CheckPhone,
    CheckPhoneIsExists,
    CheckResetPasswordPhone
};
use App\Http\Service\{
    SMS
};

class VerificationCodesController extends Controller
{
    /**
     *  注册短信验证码
     *
     */
    public function store(Request $Request)
    {
        (new CheckPhone())->gocheck();
        (new CheckPhoneIsExists())->gocheck();
        $cache_key = (new SMS())->sendRegisterCodeByPhone($Request->phone);
        return $this->responseSuccessData(['validate_key' => $cache_key]);
    }

    /**
     * 重置密码验证码
     *
     */
    public function passwordStore(Request $Request)
    {
        (new CheckResetPasswordPhone())->gocheck();
        $cache_key = (new SMS())->sendRestPasswordSMS($Request->phone);
        return $this->responseSuccessData(['validate_key' => $cache_key]);
    }
}
