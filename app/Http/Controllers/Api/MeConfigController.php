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
use Illuminate\Support\Facades\View;

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
        foreach($about_list as &$el) {
            $el['full_url'] = $_SERVER['APP_URL'] .  "/api/about_us/" . $el['type'];
        }
        return $this->responseSuccessData($about_list);
    }

    /**
     * 获取关于我们的详情
     *
     */
    public function aboutUsList($type)
    {
        $about_type_list = (new MeConfigService())->getInfoByType($type);
        return View('article', $about_type_list);
        return $this->responseSuccessData($about_type_list);
    }
}
