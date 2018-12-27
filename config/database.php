<?php

use \think\Env;

return [
    'break_reconnect'   => true,    // 断线重连
    'database'          => 'test',
    'debug'             => \Env::get('database.debug'),
    'password'          => 'root',
    // 开启自动写入时间戳字段
    'auto_timestamp' => 'datetime',
];
