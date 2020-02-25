<?php
/**
 *  验证案例订单必须存在
 *
 */
namespace App\Http\Validate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Api\Base as BaseException;
use App\Model\CaseOrder;

class CheckCaseMustBeExists// extends Base
{
    public function gocheck() 
    {
        $CaseOrderModel = new CaseOrder;
        $parameters = request()->route()->parameters();
        $User = $this->user();
        $CheckResult = Validator::make($parameters, [
            'id' => [
                'required',
                'exists:case_orders,id',
                function($attribute, $value, $fail) use ($User, $CaseOrderModel){
                    $is_order = $CaseOrderModel->where('id', $value)->where('user_id', $User->id)->first();
                    if (!$is_order) return $fail('没有这个订单');
                }
            ],
        ]) ;
        if ($CheckResult->fails()) {
            throw new BaseException([
                'msg' => $CheckResult->errors()->first()
            ]);
        }
        [
        ];
    }
}
