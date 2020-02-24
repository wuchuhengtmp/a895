<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Designer extends Model
{
    protected $table ='designer';

    protected $fillable = [
        'name',
        'longitude',
        'latitude',
        'avatar',
    ];

    /**
     *  获取设计师列表(用于案例新增)
     *
     */
    public function getList()
    {
        $Desingers = self::select(['id', 'name'])->get();
        $result = [];
        foreach($Desingers as $desinger_info)
        {
            $result[$desinger_info->id] = $desinger_info->name;
        }
        return $result;
    }
}
