<?php

/*
 *  系统内部错误异常
 */
namespace App\Exceptions\Api;

class SystemErrorException extends Base
{
    public $msg = '系统内部错误';
    public $code = 404;
}

