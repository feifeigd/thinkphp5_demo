<?php
/**
 * Created by PhpStorm.
 * User: luo fei
 * Date: 2018/12/27
 * Time: 16:34
 */

/// 缓存配置
return [
  // 使用复合缓存类型
  'type'  => 'complex',
  // 默认使用的缓存
  'default' => [
    // 驱动方式
    'type'  => 'redis',
    // 缓存有效期(秒) 0表示永久
    'expire'  => 60,
      'host'    => 'redis',
    'persistent' => true,
    'prefix'     => 'cache_',
    'select'     => 0,
  ],
  // 文件缓存
  'file'  => [
    // 驱动方式
    'type'  => 'File',
    // 缓存有效期(秒) 0表示永久
    'expire'  => 60,
    // 缓存前缀
    'prefix'  => 'cache_',
  ],
];

