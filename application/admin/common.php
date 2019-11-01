<?php

// 后台函数库

if (!function_exists('app_status')){
    function app_status($v = 0){
        $arr = [
            '未安装',
            '未启用',
            '已启用',
        ];
        if (isset($arr[$v]))
            return $arr[$v];
        return '';
    }
}
