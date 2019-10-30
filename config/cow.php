<?php
/**
 * Created by PhpStorm.
 * User: luofei
 * Date: 2019/5/4
 * Time: 1:19
 */

// +--------------------------------
// | Trace设置 开启 app_trace 后有效
// +--------------------------------
return [
    // 内置Html Console 支持扩展
    'version'               => '5.1.1',
    // 'cow_common_path'       => env('app_path').'common/',   // 公共目录
    'cow_common'            => ['common', 'function', ],
    'cow_show_index_type'   => 1,   // url生成的连接方式 1：[/a]/public/index.php/b/c, 2:[/a]/public/b/c
    'cow_open_url'          => 'http://open.cowcms.com/index.php',
];
