<?php
namespace App\Http\Validate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Api\Base as BaseException;
use App\Model\{
    User as UserModel,
    Address,
    Goods as GoodsModel,
    Order as OrderModel,
    GoodsComment as GoodsCommentModel
};

class CheckGoodsOrder extends Base
{
    /**
     * 验证规则
     */
    protected $rules = [
        'status' => [
            'required'
        ],
        'id' => [
            'required',
            'exists:orders,id'
        ],
        'goods_stars'     => [
            'required',
            'in:1,2,3,4,5'
        ],
        'service_stars'   => [
            'required',
            'in:1,2,3,4,5'
        ],
        'express_stars'   => [
            'required',
            'in:1,2,3,4,5'
        ],
        'is_comment'      => [
            'required'
        ],
        'content' => [
            'required'
        ],
        'thumb' => [
            'required'
        ]
    ];

    /**
     *  定义验证闭包挂到验证规则去
     *
     */
    public function ruleFunctions() : array
    {
        return [
            'id' => function($attribute, $value, $fail) {
                $HasData = (new OrderModel())->where('id', $value)
                    ->where('user_id', $this->user()->id)
                    ->first();
                if (!$HasData) {
                    return $fail('没这个订单');
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
            'get_order_express' => [
                'id' => function($attribute, $value, $fail) {
                    $HasData = (new OrderModel())->where('id', $value)
                        ->where('user_id', $this->user()->id)
                        ->first();
                    if (!$HasData) {
                        return $fail('没这个订单, 不能查询物流');
                    }
                    if ($HasData->status == -1) {
                        return $fail('订单取消');
                    } else if ($HasData->status == 0) {
                        return $fail('订单未支付，不能查询物流');
                    } else if ($HasData->status == 1) {
                        return $fail('订单未发货(已经支付)，不能查询物流');
                    }
                    
                }
            ],
            'save_comment'  => [
                'id' => function($attribute, $value, $fail) {
                    $Comment = (new  GoodsCommentModel)->where('order_id', request()->id)
                        ->first();
                    if ($Comment) {
                        return $fail('这个订单已经评价');
                    }
                }
            ],
            'delete' => [
                'id' => function($attribute, $value, $fail) {
                    $Order = (new OrderModel())->where('id', request()->id)
                        ->where('user_id', $this->user()->id)
                        ->first();
                    if(!$Order) {
                        return $fail('没有这个订单!');
                    }
                }
            ],
            'refund' => [
                'id' => function($attribute, $value, $fail) {
                    $Order = (new OrderModel())->where('id', $value)
                        ->where('user_id', $this->user()->id)
                        ->first();
                    switch($Order->refund_status) {
                        case -1:
                            //
                            break;
                        case 0:
                            break;
                        case 1:
                            return $fail('退款申请中，请忽重复申请');
                            break;
                        case 2:
                            return $fail('退款申请成功，请忽再次申请');
                            break;
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
        'status.required'          => '订单状态不能为空',
        'id.required'              => '订单id不能为空',
        'id.exists'                => '没有这个订单',
        'goods_stars.required'     => '商品评价星不能为空',
        'goods_stars.in'           => '商品评价为1-5',
        'service_stars.required'   => '服务评价星不能为空',
        'service_stars.in'         => '服务器评价为1-5',
        'express_stars.required'   => '物流评价星不能空',
        'express_stars.in'         => '物流评价为1-5',
        'comment_content.required' => '评价内容不能为空'
    ];

    /**
     * 验证场景
     *
     */
    protected $scene = [
        'get_order_list' => [
            'status',
        ],
        'get_order_express' => [
            'id'
        ],
        'get_order_detail' => 
        [
            'id'
        ],
        'save_comment' => [
            'id',
            'goods_stars',
            'service_stars',
            'express_stars',
            'content'
        ],
        'delete' => [
            'id'
        ],
        'refund' => [
            'id',
            'thumb',
            'content'
        ]
    ];
}
