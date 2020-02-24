<?php
namespace App\Http\Validate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Api\Base as BaseException;
use App\Model\User as UserModel;

class Base
{
    public $rules = [];

    public $messages = [];

    public $scene = [];

    public $parameters;
    
    /**
     * init parameters
     *
     */
    public function __construct()
    {
        $rute_paramters = requst()->route->paramerts();
        $request_pramaters = requst()->all();
        $this->paramerts = array_merge($result_rules, $result_rules);
         
    }

    public function gocheck()
    {


    }

    /**
     *   定制场景
     *   
     *  @crene_name 场景名
     */
    public function scene(?string $crene_name): void
    {
        $result_rules = []; 
        foreach($this->scene as $rule_name) {
            if (in_array($rule_name, $this->rules)) {
                $result_rules[$rule_name] = $this->rules[$rule_name];
            }
        }
    }
    

}
