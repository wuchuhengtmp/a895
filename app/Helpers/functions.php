<?php

use App\Model\Config;

/**
 *  获取自定义配置
 *
 */
function get_config(string $config_name) : string
{
    $Config = Config::where('name', $config_name)->first();
    return $Config->value;
}

