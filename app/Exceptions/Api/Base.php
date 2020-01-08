<?php

namespace App\Exceptions\Api;

use \Exception;

class Base extends Exception
{
    public $msg = '参数不足';
    public $code = 404;

    public function __construct(array $params = [])//: void
    {
        if (array_key_exists('code', $params)) {
            $this->code = $params['code'];
        }
        if (array_key_exists('msg', $params)) {
            $this->msg = $params['msg'];
        }
    }
}
