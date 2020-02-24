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

    /**
     *  查看全部数据
     *
     * @http GET
     */
//    public function index()
//    {
//
//    }

    /**
     *   查看单条数据
     *
     */
//    public function show()
//    {
//
//    }

    /**
     * 删除单条数据
     *
     * @http  delete
     */
//    public function destroy()
//    {
//
//    }

    /**
    *  更新
     *
     * @http   update
    *
    */
//    public function update()
//    {
//
//    }

/**
 *   对象 大驼峰
 *   数组  复数单词 戓者 结尾加_list  users 或 user_list
 *   字符串 蛇形   $hello_wordl = 'hello '
 *  方法命名 小驼峰
 *   函数命名  蛇形
 *
 */


}
