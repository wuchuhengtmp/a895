<?php

namespace App\Http\Service;

use App\Exceptions\Api\Base as BaseException;
use App\Model\{
    SignLog   as SignLogModel,
    CreditLog as CreditLogModel,
    User      as UsersModel
};
use App\Http\Logic\{
    Users  as UsersLogic,
    Credit as CreditLogic
};

class CreditLog extends Base
{
    /**
     *  登记签到积分
     *
     */
    public function logSignCreditByUserId(int $user_id)
    {
        $sign_list = (new CreditLogic())->getSignDaysByUserId($user_id);
        $credit_list = (new UsersLogic())->getCreditList(); 
        $key = count($sign_list);
        
        $credit = $credit_list[$key]['credit'];
        $CreditLogModel = new CreditLogModel();
        $CreditLogModel->title = '签到';
        $CreditLogModel->total = (int) $credit;
        $CreditLogModel->status = 1;
        $CreditLogModel->user_id= $user_id;
        $is_save = $CreditLogModel->save();
        $User = UsersModel::where('id', $user_id)->first();
        $User->credit += $credit;
        $User->save();
    }

} 

