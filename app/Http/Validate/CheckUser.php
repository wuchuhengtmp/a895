<?php
/**
 * 用户验证器
 *
 */
namespace App\Http\Validate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Api\Base as BaseException;
use App\Model\User as UserModel;
use App\Model\Address;

class CheckUser extends Base
{
    /**
     * 验证规则
     */
    protected $rules = [
        'name' => [
            'required'
        ],
        'phone' => [
            'required',
            'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/',
        ],
        'city_code' => [
            'required',
            'exists:china_area,code'
        ],
        'address' => [
            'required'
        ],
        'address_id' => [
            'required'
        ]
        
    ];

    /**
     验证闭包挂到验证规则去
     *
     */
    public function ruleFunctions() : array
    {
        return[ 
            'address_id' => function($attribute, $value, $fail) {
                $Address = (new Address())->where('id', request()->address_id)
                    ->where('user_id', $this->user()->id)
                    ->first();
                if (!$Address)  {
                    return $fail('没这个地址');
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
        ];
    }

    /**
     * 错误消息
     *
     */
    protected $messages = [
        'name.required'      => '姓名不能为空',
        'phone.required'     => '手机不能为空',
        'phone.regex'        => '手机格式不正确',
        'city_code.required' => '请选城市',
        'city_code.exists'   => '该城市不存在',
        'address.required'   => '地址不能为空'
    ];

    /**
     * 验证场景
     *
     */
    protected $scene = [
        'add_address' => [
            'name',
            'phone',
            'city_code',
            'address'
        ],
        'patch_address' => [
            'address_id'
        ],
        'del_addr' => [
            'address_id'
        ]
    ];
}
