<?php
/**
 *  验证座标参数
 *
 */
namespace App\Http\Validate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Api\Base as BaseException;
use App\Model\User as UserModel;

class CheckLocationParams
{
    public function gocheck()
    {
        $Request = request();
        $CheckResult = Validator::make($Request->all(), [
            'longitude' => 'required',
            'latitude'   => 'required'
        ], [
            'longitude.required' => '经度不能为空',
            'latitude.required' => '纬度不能为空',
        ]);
        if ($CheckResult->fails()) {
            throw new BaseException([
                'msg' => $CheckResult->errors()->first()
            ]);
        }
    }
}
