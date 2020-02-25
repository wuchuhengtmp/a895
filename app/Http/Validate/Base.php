<?php
namespace App\Http\Validate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Api\{
    Base as BaseException,
    SystemErrorException
};
use App\Model\{
    User as UserModel,
};

class Base
{
    protected  $rules = [];

    protected $messages = [];

    protected $scene = [];

    protected $parameters;
    
    /**
     * init parameters
     */
    public function __construct()
    {
        $route_paramters =request()->route() ? request()->route()->parameters() : [];
        $request_pramaters = request()->all();
        $this->paramerts = array_merge($route_paramters, $request_pramaters);
        // 载入闭包验证
        $rules = $this->ruleFunctions();
        if (($rules) > 0) {
            foreach($rules as $rule_name => $value) {
                if(array_key_exists($rule_name,$this->rules)) {
                    $this->rules[$rule_name][] = $value;
                }
            }
        }
    }

    /**
     * 进行验证
     *
     */
    public function gocheck()
    {
        $CheckResult = Validator::make(
            $this->paramerts,
            $this->rules,
            $this->messages
        );
        if($CheckResult->fails()) {
            throw new BaseException([
                'msg' => $CheckResult->errors()->first()
            ]);
        }

    }

    /**
     *   定制场景
     *   
     *  @crene_name 场景名
     */
    public function scene(?string $crene_name)
    {
        $result_rules = []; 
        if (!array_key_exists($crene_name, $this->scene)) {
           throw new SystemErrorException('没这个场景验证');
        }
        // 收集验证规则
        foreach($this->scene[$crene_name] as $rule_name) {
            if (array_key_exists($rule_name, $this->rules)) {
                $result_rules[$rule_name] = $this->rules[$rule_name];
            }
        }
        if (count($result_rules) > 0) {
            $this->rules = $result_rules;
        }
        return $this;
    }

    /**
     *  定义验证闭包挂到验证规则去
     *
     */
    public function ruleFunctions() : array
    {
        return []; 
    }

    /**
     * 用户
     *
     */
    public function user()
    {
        $headers = request()->header();
        if (!array_key_exists('authorization', $headers)) {
            throw new SystemErrorException('请登录');
        } 
        [$token, ] = $headers['authorization'];
        [, $user_info]= explode('.', $token);
        $user_info= base64_decode($user_info);
        $UserInfo = json_decode($user_info);
        $User = (new UserModel())->where('id', $UserInfo->sub)->first();
        if (!$User) {
            throw new SystemErrorException('没有这个用户');
        } else {
            return $User;
        }
    }
}
