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

    /// 文件写入
    /// @param $filename string 文件路径
    /// @param $data string 文件要写入的内容
    /// @return bool
    public function write($filename = '', $data = ''){
        $pathinfo = pathinfo($filename);
        $dir = $pathinfo['dirname'];
        // $file = $pathinfo['basename'];
        if(!$this->d_has($dir))
            mkdir($dir, 0777, true);
        $is = file_put_contents($filename, $data);
        return $is;
    }

    /// 文件删除
    /// @param $filename string 文件路径
    /// @return array
    public function f_delete($filename){
        return @unlink($filename);
    }

    /// 判断文件或者文件夹是否可写
    /// @param $file string 文件或者文件夹路径
    /// @return bool
    function is_write($file){
        $pathinfo = pathinfo($file);
        $dir = $pathinfo['dirname'];
        $extension = isset($pathinfo['extension']) ? $pathinfo['extension'] : null;
        if($extension){
            $is = $this->write($file);
            return $is;
        }
        $filename = substr($file, 0, 1) == '/' ? $file.'is_write_000000123456789.txt' : $file.DIRECTORY_SEPARATOR.'is_write_000000123456789.txt';
        $is = $this->write($filename, 'OK');
        if($is){
            $this->f_delete($filename);
            return true;
        }
        return false;
    }
}
