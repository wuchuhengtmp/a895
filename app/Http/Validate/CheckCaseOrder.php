<?php
/**
 *  项目订单验证
 *
 */
namespace App\Http\Validate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Api\Base as BaseException;
use App\Model\{
    User as UserModel,
    CaseOrder
};

class CheckCaseOrder extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->CaseOrderModel = new CaseOrder();
    }
    /**
     * 验证规则
     */
    protected $rules = [
        'case_id'  => [
            'required',
            'exists:cases,id'
        ],
        'area' => [
            'required',
            'int',
            'gt:0'
        ],
        'room' => [
            'required',
        ],
        'city_code' => [
            'required',
            'exists:china_area,code'
        ],
        'phone' => [
            'required',
            'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/',
        ],
        'name' => [
            'required'
        ],
        'pay_type' => [
            'required',
            'in:wechat,alipay'
        ],
        'id' => [
            'required',
            'exists:case_orders,id'
        ],
        'app_pay_type' => [
            'required',
            'in:total,installment',
        ],
        'installment' => [
            'required'
        ],
        'times' => [
            'required_if:app_pay_type,installment',
            'int',
            'gt:1'
        ],
        'compact_url' => [
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
            // 订单id是否是当前人所有验证
            'id' => function($attribute, $value, $fail) {
                $is_order = $this->CaseOrderModel->where('id', $value)->where('user_id', $this->User()->id)->first();
                if (!$is_order) return $fail('没有这个订单');
            }
        ]; 
    }

    /**
     * 验证场景验证扩展
     */
    public function sceneExtendRules (): array
    {
        return [
            'verify_application' => [
                'id' => function($attribute, $value, $fail) {
                    $Order = $this->CaseOrderModel->where('id', $value)->where('user_id', $this->User()->id)->first();
                    if (!$Order) return $fail('没有这个订单');
                    if (!in_array($Order->status, [100, 202])) {
                        $messages = [
                            201 => '您正在申请中,请忽重复申请',
                            200 => '您已经申请成功了,请不要重复申请',
                            300 => '您已经申请成功了,请不要重复申请',
                            301 => '您已经申请成功了,请不要重复申请',
                            302 => '您已经申请成功了,请不要重复申请',
                            303 => '您已经申请成功了,请不要重复申请',
                            400 => '您已经申请成功了,请不要重复申请',
                        ];
                        return $fail($messages[$Order->status]);
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
        'case_id.required'   => '项目id不能为空',
        'case_id.exists'     => '项目id不存在',
        'area.required'      => '面积不能为空',
        'room.required'      => '户型不能为空',
        'city_code.required' => '城市不能为空',
        'city_code.exists'   => '没有这个城市',
        'phone.required'     => '手机不能为空',
        'phone.regex'        => '手机格式不正确',
        'name.required'      => '用户名不能为空',
        'pay_type.required'  => '支付方式不能为空',
        'pay_type.in'        => '支付方式请选择wechat 或 alipay',
        'id.exists'          => '没有这个订单',
        'app_pay_type.required' => '支付方式不能为空',
        'app_pay_type.in' => '支付方式为total或者installment',
        'compact_url.required' => '合同图片不能为空',
        'times.required_if' => '分期不能为空',
        'times.gt' => '分期不能小于1',
    ];

    /**
     * 验证场景
     *
     */
    protected $scene = [
        // 生成订单验证
        'create_order'  => [
            'case_id',
            'area',
            'room',
            'city_code',
            'phone',
            'name',
            'pay_type'
        ],
        // 订单详情
        'get_order' => [
            'id'
        ],
        // 提交合约审核
        'verify_application'  => [
            'id',
            'app_pay_type',
            'compact_url',
            'times'
        ],
    ];
}
