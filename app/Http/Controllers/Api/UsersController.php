<?php

namespace App\Http\Controllers\Api;

use function Couchbase\defaultDecoder;
use Illuminate\Http\Request;
use App\Http\Validate\{
    CheckPhoneRegister,
    CheckResetPassword 
};
use App\Http\Service\{
    User as UserService
};
use App\Http\Validate\{
    CheckUserExists,
    CheckLocationParams
};

class UsersController extends Controller
{
    /**
     * 用户注册
     */
    public function store(Request $Request)
    {
        (new CheckPhoneRegister())->gocheck();
        (new UserService())->registerStore();
        \Cache::forget(request()->validate_key); 
        return $this->responseSuccess();
    }

    /**
     * 重置密码 
     *
     */
    public function updatePassword(Request $Request)
    {
        (new CheckResetPassword())->gocheck();
        (new UserService())->resetPassword();
        \Cache::forget(request()->validate_key); 
        return $this->responseSuccess();
    }

    /**
     * 座标信息
     *
     */
    public function location(Request $Request)
    {
        (new CheckLocationParams())->gocheck();
	$location_info = get_location($Request->input('longitude'), $Request->input('latitude'));
	return $this->responseSuccessData([
            'city'              => $location_info['regeocode']['addressComponent']['city'],
            'province'          => $location_info['regeocode']['addressComponent']['province'],
            'code'              => $location_info['regeocode']['addressComponent']['adcode'],
            'formatted_address' => $location_info['regeocode']['formatted_address']
	]);
    }

}
