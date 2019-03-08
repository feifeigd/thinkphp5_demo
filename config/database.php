<?php

use \think\Env;

return [
    'break_reconnect'   => true,    // 断线重连
    'database'          => 'www_daikuan2345_',
    'debug'             => \Env::get('database.debug'),
    'password'          => \Env::get('database.password') ?: 'Zws07533601711swZROOT',
    // 数据库表前缀
    'prefix'          => 'pay_',
    // 开启自动写入时间戳字段
    'auto_timestamp' => 'datetime',
];
