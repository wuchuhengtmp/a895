<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\User;
use \Auth;
use App\Http\Service\{
    SMS
};
use Illuminate\Support\Facades\Cache;

class IndexController extends Controller
{
    public function shareShow()
    {
        $data = [];
        if (request()->method() === 'POST') {
            if (request()->send) {
                request()->validate([
                    'phone' => [
                        'required',
                        'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/',
                        'unique:users,phone'
                    ]
                ], [
                    'phone.required' => '请输入手机号',
                    'phone.regex'    => '手机号格式不正确',
                    'phone.unique'   => '手机已注册',
                ]);
                $cache_key = (new SMS())->sendRegisterCodeByPhone(request()->phone);
                session()->flash('success', '验证码发送成功！');
                $data['validator'] = $cache_key;
                $data['phone'] = request()->phone;

                // 注册验证
            } else if(request()->register)  {
                request()->validate([
                    'validator' => [
                        'required',
                    ],
                    'code' => ['required'],
                    'validator' => 'required'
                ]);
                $phone_info = Cache::get(request()->validator);
            }
        }
        
        return view('index/share', $data);
    }

    public function shareStore()
    {
        // 短信验证

    }
}
