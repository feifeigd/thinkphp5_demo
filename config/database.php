<?php

use \think\Env;

return [
    'break_reconnect'   => true,    // 断线重连
    'database'          => 'test',
    'debug'             => \Env::get('database.debug'),
    'password'          => 'root',
];
