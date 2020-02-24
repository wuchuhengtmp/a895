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
