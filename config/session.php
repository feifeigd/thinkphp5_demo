<?php
/**
 * Created by PhpStorm.
 * User: luo fei
 * Date: 2018/12/27
 * Time: 16:13
 */

/// 会话设置
return [
  // 驱动方式 支持redis memcache memcached
  'type'  => 'redis',
  // 是否自动开启SESSION
  'auto_start'  => true,
  'select'       => 2, // 操作库 0~15
  'expire'       => 1800, // 有效期(秒)
  'session_name' => 'session_', // sessionkey前缀
];
