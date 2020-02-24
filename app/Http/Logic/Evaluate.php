<?php

namespace App\Http\Logic;
use App\Model\{
    User as UserModel
};

class Evaluate
{
    /**
     *  时间戳相减获取并转换为对应日期时间
     */
    public function getTimeDiff($start_time,$end_time)
    {
        $timeDiff = $end_time - $start_time;
        $days = intval( $timeDiff / 86400 );
        $remain = $timeDiff % 86400;
        $hours = intval( $remain / 3600 );
        $remain = $remain % 3600;
        $mins = intval( $remain / 60 );
        $secs = $remain % 60;
        $res = array( "day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs );
        return $res;
    }

    /**
     *  组装评论列表
     */
    public function getEvaluate($userEvaluate){
        for($i=0;$i<count($userEvaluate);$i++){
            $userEvaluate[$i]['img'] = env('APP_URL').'/uploads/'.$userEvaluate[$i]['img'];
            $timeInfo = $this->getTimeDiff(strtotime($userEvaluate[$i]['time']),time());
            if($timeInfo['day'] >= 365){
                $userEvaluate[$i]['time'] = '1 year ago';
            }elseif ($timeInfo['day'] != 0){
                if($timeInfo['day'] == 1){
                    $userEvaluate[$i]['time'] = $timeInfo['day'].' day ago';
                }else{
                    $userEvaluate[$i]['time'] = $timeInfo['day'].' days ago';
                }
            }elseif ($timeInfo['day'] == 0 && $timeInfo['hour']!=0){
                if($timeInfo['hour'] == 1){
                    $userEvaluate[$i]['time'] = $timeInfo['hour'].' hour ago';
                }else{
                    $userEvaluate[$i]['time'] = $timeInfo['hour'].' hours ago';
                }
            }elseif ($timeInfo['hour'] == 0 && $timeInfo['min']!=0){
                if($timeInfo['min'] == 1){
                    $userEvaluate[$i]['time'] = $timeInfo['min'].' minute ago';
                }else{
                    $userEvaluate[$i]['time'] = $timeInfo['min'].' minutes ago';
                }
            }else{
                if($timeInfo['sec'] == 1){
                    $userEvaluate[$i]['time'] = $timeInfo['sec'].' second ago';
                }else{
                    $userEvaluate[$i]['time'] = $timeInfo['sec'].' seconds ago';
                }
            }
            $user_info = UserModel::where('id',$userEvaluate[$i]['user_id'])->first();
            $userEvaluate[$i]['avatar'] = env('APP_URL').'/uploads/'.$user_info['avatar'];
            $userEvaluate[$i]['nickname'] = $user_info['nickname'];
            unset($userEvaluate[$i]['user_id']);
        }
        return $userEvaluate;
    }
}
