<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CaseCategory extends Model
{
    protected $table = 'case_category';

    /**
     * 获分类列表(用于案例表单)
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

    /** 
     * 获分类列表(用于api接口)
     *
     */
    public function getListForApi()
    {
        $categores = self::select(['id', 'name'])->get()->toArray();
        return $categores;
    }
}
