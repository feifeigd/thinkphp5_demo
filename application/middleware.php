<?php
/**
 * Created by PhpStorm.
 * User: luo fei
 * Date: 2018/12/27
 * Time: 14:00
 */

/// 全局中间件的注册
/// 默认全局中间件存放的位置是http/middleware
/// 注册的顺序, 就是调用的顺序
/// 先按顺序执行全局中间件，再按顺序执行模块中间件
return [
  'InAppCheck',
  'Check',
];
