<?php

namespace App\Http\Logic;

use App\Model\{
    CreditLog  as CreditLogModel
};

class CreaditRecord
{
    /**
     *  新增用户积分记录
     */
    public function creaditRecordAdd($user_id,$title,$total,$status)
    {
        $insert = [
            'title' => $title,
            'total' => $total,
            'user_id' => $user_id,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        $bool = CreditLogModel::insert($insert);
        if(!$bool){
            return false;
        }
        return true;
    }
}
