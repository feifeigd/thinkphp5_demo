<?php
/**
 * Created by PhpStorm.
 * User: luofei
 * Date: 2019/5/1
 * Time: 9:44
 */

/// 模块中间件
/// 默认全局中间件存放的位置是http/middleware
/// 注册的顺序, 就是调用的顺序
/// 先按顺序执行全局中间件，再按顺序执行模块中间件
return [
    'Hello',
];
