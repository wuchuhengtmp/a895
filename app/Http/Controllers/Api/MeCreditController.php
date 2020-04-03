<?php

namespace App\Http\Controllers\Api;

use function Couchbase\defaultDecoder;
use Illuminate\Http\Request;
use App\Http\Validate\{
    CheckUserExists
};
use App\Http\Service\{
    MeCredit as MeCreditService
};
use App\Exceptions\Api\Base as BaseException;

class MeCreditController extends Controller
{
    /**
     * 获取任务列表
     *
     */
    public function getTaskList()
    {
        (new CheckUserExists())->gocheck();
        $taskList = (new MeCreditService())->getTaskList();
        return $this->responseSuccessData($taskList);
    }

    /**
     * 积分转账
     *
     */
    public function transferAccounts(Request $Request)
    {
        (new CheckUserExists())->gocheck();
        if(!$this->user()->transfer_pwd) {
            throw new BaseException(['msg' => '请设置转账密码', 'code' =>403]);
        }
        
        (new MeCreditService())->transferAccounts($this->user()->id);
        return $this->responseSuccess();
    }

    /**
     * 获取积分明细记录
     *
     */
    public function meCreditList()
    {
        (new CheckUserExists())->gocheck();
        $list = (new MeCreditService())->meCreditList($this->user()->id);
        return $this->responseSuccessData($list);
    }

}
