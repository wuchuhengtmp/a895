<?php
namespace App\Http\Validate;

use Illuminate\Http\Request;
use App\Model\{
    PayTimes as PayTimesModel
};

class CheckPayTimes extends Base
{
    /**
     * 验证规则
     */
    protected $rules = [
        'order_id' => [
            'required',
            'exists:case_orders,id'
        ],
        'id' => [
            'required',
            'exists:pay_times,id'
        ],
        'image1' => [
            'required_without:image2'
        ],
        'image2' => [
            'required_without:image1'
        ]
    ];

    /**
     *  定义验证闭包挂到验证规则去
     */
    public function ruleFunctions() : array
    {
        return [
            'id' => function($attribute, $value, $fail) {
                $PayTime = (new PayTimesModel())->where('id', $value)
                    ->first();
                if (!$PayTime) {
                    return $fail('没这个分期id');
                }
                if ($PayTime->caseOrder->user->id !== $this->user()->id) {
                    return $fail('没分期id不属于当前用户');
                }
            }
        ]; 
    }

    /**
     * 验证场景验证扩展
     */
    public function sceneExtendRules (): array
    {
        return [
            // 申请分期场景附加验证
            'application' => [
                'id' => function($attribute, $value, $fail) {
                    $PayTime = (new PayTimesModel())
                        ->where('id', $value)
                        ->first();
                    if (!$PayTime) {
                        return $fail('没这个分期id');
                    }
                    switch($PayTime->status) {
                        case 100:
                            return $fail('分期已支付');
                        case 101;
                            return $fail('您已经提交支付申请，审核期间请忽重复提交');
                    }
                }
            ]
        ];
    }

    /**
     * 错误消息
     *
     */
    protected $messages = [
        'order_id.required' => '订单id不能为空',
        'order_id.exists'   => '没有这个订单',
        'id.required'       => '分期id不能为空',
        'id.exists'         => '没有这个分期',
        'image1.required_without' => '请上传至少一张图片',
        'image2.required_without' => '请上传至少一张图片',
    ];

    /**
     * 验证场景
     *
     */
    protected $scene = [
        // 获取分期表
        'get_pay_times_list' => [
            'order_id'
        ],
        // 分期申请
        'application' => [
            'id',
            'image1',
            'image2'
        ]
    ];
}
