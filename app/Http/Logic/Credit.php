<?php

namespace App\Http\Logic;

use App\Model\{
    Config  as ConfigModel,
    SignLog as SignLogModel
};

class Credit
{
    /**
     *  用户已经签到天列表
     */
    public function getSignDaysByUserId(int $user_id)
    {
            $SignLoges = SignLogModel::where('user_id', $user_id)
                ->limit(7)
                ->orderBy('id', 'DESC')
                ->select(['created_at'])
                ->get();
            $sign_list = $SignLoges->toArray();
            foreach($sign_list as $key=>$sign){
                $current_time = $sign['created_at'];
                $current_time = strtotime($current_time);
                $current_time = date('Y-m-d', $current_time);
                $current_time = strtotime($current_time);
                if (!isset($time)) {
                    $time = $current_time;
                    continue;
                }
                if (($current_time + 60 * 60 * 24) === $time ) {
                    $time = $current_time;
                } else {
                    unset($sign_list[$key]);
                }
            }
            $sign_list = array_column($sign_list, 'created_at');
            $sign_list = array_reverse($sign_list);
            return $sign_list;
    }
}
