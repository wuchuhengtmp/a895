<?php
/**
 * 搜索关键词
 *
 */
namespace App\Http\Validate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Api\Base as BaseException;
use App\Model\User as UserModel;

class CheckKeyWordsMustBeExists
{
    public function gocheck()
    {
        $Request = request();
        $CheckResult = Validator::make($Request->all(), [
            'keyword' => 'required'
        ], [
            'keyword.required' => '搜索关键词不能为空'
        ]); 
        if ($CheckResult->fails()) {
            throw new BaseException([
                'msg' => $CheckResult->errors()->first()
            ]);
        }
    }
}
