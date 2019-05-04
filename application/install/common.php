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

/// 判断系统类模块函数是否被支持
/// @param $data array 系统设置参数
/// @return array
function get_php_system($data){
    if(!is_array($data) || count($data) <= 0) return [];
    $res = [];
    foreach($data as $k => $v){
        if(!$v)continue;
        if(is_callable($v)){
            $param = ['key'=>$k];
            $res[] = $v[$param];
        }elseif($v == 'class')
            $res[] = [$k, '类', class_exists($k)];
        elseif($v == 'function')
            $res[] = [$k, '函数', function_exists($k)];
        elseif($v == 'model')
            $res[] = [$k, '模块', extension_loaded($k)];
        else $res[] = [$k, '未知', false];
    }
    return $res;
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

/// 环境检测
/// @param $data array 系统设置参数
/// @return array
function is_system($data){
    if(!is_array($data) || count($data) <= 0) return [];
    $res = [];
    foreach ($data as $v){
        if(!$v || !is_array($v))continue;
        $is = is_callable($v[2]) ? $v[2]($v[1]) : $v[2];
        $res[] = [$v[0], $v[1], $is];
    }
    return $res;
}
