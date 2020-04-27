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
        $floor_date = date('Y-m-d', strtotime(date('Y-m-d')) - 7 * 24 * 60 * 60);
        $yestoday = date('Y-m-d', time() - 24 * 60 * 60);
        $HasYestodaySign = SignLogModel::where('user_id', $user_id)
            ->whereBetween('created_at', [$yestoday . ' 00:00:00', $yestoday . ' 23:59:59'])
            ->limit(1)
            ->get();
        if ($HasYestodaySign->isEmpty()) {
            $HasTodaySign = SignLogModel::where('user_id', $user_id)
                ->whereBetween('created_at', [date('Y-m-d'). ' 00:00:00', date('y-m-d'). ' 23:59:59'])
                ->limit(1)
                ->get();
            if ($HasTodaySign->isEmpty()) {
                return [];
            } else {
                return [
                    'created_at' => $HasTodaySign->first()->created_at
                ];
            }
        }
        $SignLoges = SignLogModel::where('user_id', $user_id)
            ->where('created_at', '>=', $floor_date)
            ->limit(7)
            ->orderBy('id', 'DESC')
            ->select(['created_at'])
            ->get();
        if ($SignLoges->isNotEmpty()) {
            $return_arr = [];
            $pre_date = null;
            foreach($SignLoges as $SingLog) {
                $current_date = strtotime(date('Y-m-d', $SingLog->created_at->timestamp));
                if ($pre_date === null) {
                    $pre_date = $current_date + 24 * 60 * 60;
                } 
                if($pre_date && ($pre_date - 24 * 60 * 60) === $current_date) {
                    $return_arr[] = ['created_at'=> date('Y-m-d H:i:s', $current_date)];
                    $pre_date = $current_date;
                } else {
                    return $return_arr;
                }
            }
        } else {
            return [];
        }

    }
}
