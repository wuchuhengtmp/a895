<?php
/**
 *  验证案例必须存在
 *
 */
namespace App\Http\Validate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Api\Base as BaseException;
use App\Model\User as UserModel;

class CheckCaseMustBeExists
{
    public function gocheck()
    {
        $parameters = request()->route()->parameters();
        $CheckResult = Validator::make($parameters, [
            'id' => 'required|exists:cases,id',
        ], [
            'id.required' => '案例id不能为空',
            'id.exists' => '案例不存在',
        ]);
        if ($CheckResult->fails()) {
            throw new BaseException([
                'msg' => $CheckResult->errors()->first()
            ]);
        }
    }
}
