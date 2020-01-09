<?php

namespace App\Http\Logic;

use App\Model\{
    Config    as ConfigModel,
};

class Users
{
    /**
     *  获取积分签到列表
     *
     */
    public function getCreditList()
    {
        $CreditList = ConfigModel::whereIn(
            'name', 
            [ 
                'SING_1',
                'SING_2',
                'SING_3',
                'SING_4',
                'SING_5',
                'SING_6',
                'SING_7',
            ])
            ->select([
                'value as credit'
            ])
            ->orderBy('name', 'ASC')
            ->get();
        $CreditList->each(function($item, $key) {
            static $id = 0;
            $id++;
            $item->id = $id;
        });
        return $CreditList->toArray();
    }
}

