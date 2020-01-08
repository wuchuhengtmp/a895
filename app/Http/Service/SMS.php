<?php

namespace App\Http\Service;

use App\Exceptions\Api\Base as BaseException;

class SMS
{
    /**
     *  注册模板
     */
    protected $register_template;

    /**
     * 重置密码模板
     */
    protected $reset_password_template;

    /**
     * 初始化参数 
     *
     */
    public function __construct()
    {
        $this->register_template       = env('SMS_VALIDATE_TEMPLATE');
        $this->reset_password_template = env('SMS_RESETPASSWORD_TEMPLATE');
    }

    /**
    * 发送注册验证码
    *
    */
    public function sendRegisterCodeByPhone(int $phone): string
    {
        $sms = app('easysms');
        $code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);  
        $key = 'verificationCode_'.str_random(15);
        $expiredAt = now()->addMinutes(10);
        \Cache::put($key, [
            'phone' => $phone,
            'code'  => $code,
            'type'  => 'register'
        ], $expiredAt);  
        try{
            $sms->send($phone, [
                'template' => $this->register_template,
                'data' => [
                    'code' => $code
                ],
            ]);
        } catch(\Exception $E) {
           $message = $E->getExceptions()['aliyun']->raw['Message'];
            throw new BaseException([
                'msg' => $message
            ]);
        }
        return $key;
    }

    /**
     * 重置密码短信
     * 
     */
    public function sendRestPasswordSMS(int $phone)
    {
        $sms = app('easysms');
        $code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);  
        $key = 'verificationCode_'.str_random(15);
        $expiredAt = now()->addMinutes(10);
        \Cache::put($key, [
            'phone' => $phone,
            'code'  => $code,
            'type'  => 'reset_password'
        ], $expiredAt);  
        try{
            $sms->send($phone, [
                'template' => $this->reset_password_template,
                'data' => [
                    'code' => $code
                ],
            ]);
        } catch(\Exception $E) {
           $message = $E->getExceptions()['aliyun']->raw['Message'];
            throw new BaseException([
                'msg' => $message
            ]);
        }
        return $key;
    }
}
