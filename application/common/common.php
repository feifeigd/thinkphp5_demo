<?php
/**
 * Created by PhpStorm.
 * User: luofei
 * Date: 2019/5/4
 * Time: 2:29
 */


/// 判断模块是否安装
/// @param sign string 模块目录名称
/// @return bool
function isInstall($sign){
    $F = new files\File();
    $installPath = env('app_path').$sign.DIRECTORY_SEPARATOR;
    return $F->f_has($installPath.'install.install');
}

/// 判断是否有模块
/// @param $sign string 模块目录名称
/// @return bool
function isModule($sign){
    $F = new \files\File();
    $installPath = env('app_path').$sign.DIRECTORY_SEPARATOR;
    return $F->d_has($installPath);
}

