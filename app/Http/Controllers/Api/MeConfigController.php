<?php

namespace App\Http\Controllers\Api;

use function Couchbase\defaultDecoder;
use Illuminate\Http\Request;
use App\Http\Service\{
    User as UserService,
    MeConfig as MeConfigService
};
use App\Http\Validate\{
    CheckUserExists
};

class MeConfigController extends Controller
{
    /**
     * 修改登录密码
     *
     */
    public function userPwdUpdate(Request $Request){
        (new CheckUserExists())->gocheck();
        (new MeConfigService())->userPwdUpdate();
        \Cache::forget(request()->validate_key);
        return $this->responseSuccess();
    }

    /**
     * 转账密码设置
     *
     */
    public function transferPwd(Request $Request){
        (new CheckUserExists())->gocheck();
        (new MeConfigService())->transferPwd($this->user()->id);
        return $this->responseSuccess();
    }

    /**
     * 意见反馈
     *
     */
    public function feedback(Request $Request){
        (new CheckUserExists())->gocheck();
        (new MeConfigService())->FeedbackUpdate();
        return $this->responseSuccess();
    }

    /**
     * 获取关于我们的分类列表
     *
     */
    public function aboutUs()
    {
        (new CheckUserExists())->gocheck();
        $about_list = (new MeConfigService())->getTypeList();
        return $this->responseSuccessData($about_list);
    }

    /**
     * 获取关于我们的分类信息
     *
     */
    public function aboutUsList($type)
    {
        (new CheckUserExists())->gocheck();
        $about_type_list = (new MeConfigService())->getInfoByType($type);
        return $this->responseSuccessData($about_type_list);
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
