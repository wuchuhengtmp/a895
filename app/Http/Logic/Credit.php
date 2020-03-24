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
        if (count($sign_list) >0){
            $first_time = $sign_list[0];
            $current_time = $first_time['created_at'];
            $time = strtotime($current_time);
            $str_time = date('Y-m-d', $time);
            $yesterday = date("Y-m-d", time() - 60 * 60 * 24);
            if ($str_time !== date('Y-m-d') && $str_time !== $yesterday) {
                return [];
            }
        }
        $is_lawful = 1;
        
        foreach($sign_list as $key=>$sign){
            $current_time = $sign['created_at'];
            $current_time = strtotime($current_time);
            $current_time = date('Y-m-d', $current_time);
            $current_time = strtotime($current_time);
            // 合法时间
            // 第一个
            if (!isset($pre_time)){
                $pre_time = $current_time;
            } else {
                // 往后的
                if ($current_time !== ($pre_time - 24 * 60 * 60)) {
                    $is_lawful = 0;
                }
            }
            if ($is_lawful === 0) {
                unset($sign_list[$key]);
            }
        }
        $sign_list = array_column($sign_list, 'created_at');
        $sign_list = array_reverse($sign_list);
        return $sign_list;
    }
}
