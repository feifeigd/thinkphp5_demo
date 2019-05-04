<?php
/**
 * Created by PhpStorm.
 * User: luofei
 * Date: 2019/5/4
 * Time: 17:25
 */

/// 判断文件或者文件夹是否有可写权限
/// @param $file string 文件或者文件夹路径
/// @return bool
function is_write($file){
    $F = new \files\File();
    return $F->is_write($file);
}

/// 判断文件或者文件夹是否有读写权限
/// @param $data array 系统设置参数
/// @return array
function is_write_array($data){
    if(!is_array($data) || count($data) <= 0)return [];
    $res = [];
    foreach ($data as $v){
        if(!$v)continue;
        $res[] = [$v, is_write(\App::getRootPath().$v)];
    }
    return $res;
}

