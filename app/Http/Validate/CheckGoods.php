<?php
namespace App\Http\Validate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Api\Base as BaseException;
use App\Model\{
    User as UserModel,
    Address,
    Goods as GoodsModel
};

class CheckGoods extends Base
{
    /**
     * 验证规则
     */
    protected $rules = [
        'id' => [
            'required',
            'exists:goods,id'
        ],
        'address_id' => [
            'required'
        ],
        'pay_type' => [
            'in:wechat,alipay'
        ],
        'total' => [
            'required',
            'int',
            'gt:1'
        ],
        'pay_type' => [
            'required',
            'in:wechat,alipay'
        ]
    ];

    /**
     *  定义验证闭包挂到验证规则去
     *
     */
    public function ruleFunctions() : array
    {
        return [ ]; 
    }

    /**
     * 验证场景验证扩展
     */
    public function sceneExtendRules (): array
    {
        return [
            'add_order' => [
                'address_id' => function($attribute, $value, $fail) {
                    $Addresses = (new Address())->where('id', $value)
                        ->where('user_id', $this->user()->id)
                        ->get();
                    if ($Addresses->isEmpty()) {
                        return $fail('没有这个地址');
                    }
                },
                'pay_type' => function($attribute, $value, $fail) {
                    // 积分验证
                    $Goods = (new GoodsModel())->where('id', request()->id)
                        ->select('credit')
                        ->first();
                    $be_need_credit = $Goods->credit * request()->total;
                    if ($be_need_credit > $this->user()->credit) {
                        return $fail("积分不足!本次订单需要积分{$be_need_credit},而您当前积分为" . $this->user()->credit);
                    }
                }
            ],
        ];
    }

    /**
     * 错误消息
     *
     */
    protected $messages = [
        'id.required'         => '商品id不能为空',
        'id.exists'           => '商品不存在',
        'address_id.required' => '地址id不能为空',
        'total.required'      => '商品数量不能为空',
        'total.int'           => '商品数量为正数',
        'total.gt'            => '商品数量不能小于0',
        'pay_type.required'   => '支付方式不能为空',
        'pay_type.in'         => '支付方式必须为微信或支付宝'
    ];

    /**
     * 验证场景
     *
     */
    protected $scene = [
        'get_comments' => [
            'id'
        ],
        'add_order' => [
            'id',
            'address_id',
            'total',
            'pay_type'
        ]
    ];
}
