<?php
/**
 * Created by PhpStorm.
 * User: luofei
 * Date: 2019/5/4
 * Time: 1:45
 */

namespace files;


class File
{
    /// 判断文件是否存在
    /// @param $filename 文件路径
    public function f_has($filename = ''){
        $is = is_file($filename);
        return $is;
    }

    /// 判断文件夹是否存在
    /// @param dir string 目录
    /// @return bool
    public function d_has($dir = ''){
        $is = is_dir($dir);
        return $is;
    }
}