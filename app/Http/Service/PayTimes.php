<?php

/**
 *  分期控制服务
 *
 */
namespace App\Http\Service;

use App\Model\{
    PayTimes as PayTimesModel
};

class PayTimes extends Base
{
    /**
     * 提交分期付款申请
     * @data 数据 
     */
    public function recordApplication(array $data)
    {
        $images =array($data['image1'], $data['image2']);
        $images = array_values(array_filter($images));
        $images = json_encode($images);
        $PayTimesRow  = (new PayTimesModel())->where('id', $data['id'])->first();
        $PayTimesRow->status = 101;
        $PayTimesRow->images = $images;
        if ($PayTimesRow->save()) {
            return true;
        } else {
            return false;
        }
    }
}
