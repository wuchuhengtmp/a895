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

class CheckResetPassword extends Base
{
    /**
     * 验证规则
     */
    protected $rules = [
    ];

    /**
     *  定义验证闭包挂到验证规则去
     *
     */
    public function ruleFunctions() : array
    {
        return []; 
    }

    /**
     * 验证场景验证扩展
     */
    public function sceneExtendRules (): array
    {
        return [];
    }

    /**
     * 错误消息
     *
     */
    protected $messages = [
    ];

    /**
     * 验证场景
     *
     */
    protected $scene = [
    ];
}
