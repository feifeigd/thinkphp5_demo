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
    public function f_has($filename = ''):bool{
        $is = is_file($filename);
        return $is;
    }

    /// 判断文件夹是否存在
    /// @param dir string 目录
    /// @return bool
    public function d_has($dir = ''):bool{
        $is = is_dir($dir);
        return $is;
    }

    /// 创建目录
    public function d_create($dir=''):bool
    {
        if(!$dir || $dir=="." || $dir =="./") return true;
        if(!$this->d_has($dir))
        {
            $is = mkdir($dir,0777,true);
            return $is;
        }
        return true;
    }

    /// 文件写入
    /// @param $filename string 文件路径
    /// @param $data string 文件要写入的内容
    /// @return bool
    public function write($filename = '', $data = ''):bool{
        $pathinfo = pathinfo($filename);
        $dir = $pathinfo['dirname'];
        // $file = $pathinfo['basename'];
        if(!$this->d_has($dir))
            mkdir($dir, 0777, true);
        $is = file_put_contents($filename, $data);
        return $is;
    }

    /// 文件读取
    public function read($filename='')
    {
        if($this->f_has($filename))
        {
            $content=file_get_contents($filename);
            return $content;
        }
        else
        {
            return "";
        }
    }

    /// 读取文件内容，将读取的内容放入数组中，每个数组元素为文件的一行，内容包括换行
    function read_array($filename="") : array
    {
        if($this->f_has($filename))
        {
            return  file($filename);
        }
        return [];
    }

    /// 文件删除
    /// @param $filename string 文件路径
    /// @return array
    public function f_delete($filename){
        return @unlink($filename);
    }

    /// 文件夹删除
    function d_delete($dir="") : bool
    {
        //先删除目录下的文件：

        if(!$this->d_has($dir)) return true;

        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!="..") {

                $fullpath=$dir.DIRECTORY_SEPARATOR.$file;

                if(!is_dir($fullpath))
                {
                    @unlink($fullpath);
                }
                else
                {
                    $this->d_delete($fullpath);
                }
            }
        }

        closedir($dh);
        //删除当前文件夹：
        return(rmdir($dir));
    }

    /// 拷贝文件或目录
    /// @param string $type 0为删除拷贝目录 1为不删除拷贝目录
    function copy_($new,$old,$type=1) : bool
    {
        $is=false;

        if(!file_exists($old) && !is_dir($old)) return false;
        $pathinfo_new = pathinfo($new);
        $path=isset($pathinfo_new['extension'])?$pathinfo_new['dirname']:$new;
        if(!is_dir($path))  mkdir($path, 0777, true);

        if(is_file($old))
        {
            if(!isset($pathinfo_new['extension']))
            {
                $pathinfo = pathinfo($old);
                $is = copy($old,$new. DIRECTORY_SEPARATOR . $pathinfo['basename']);
            }
            else
            {
                $is = copy($old,$new);
            }
        }
        else
        {
            if(!isset($pathinfo_new['extension']))
            {
                $dir= scandir($old);
                foreach ($dir as $filename )
                {
                    if(!in_array($filename,array('.','..')) )
                    {
                        if(is_dir($old.DIRECTORY_SEPARATOR.$filename))
                        {
                            $is = $this->copy_($new.DIRECTORY_SEPARATOR.$filename,$old.DIRECTORY_SEPARATOR.$filename,$type);
                            if(!$is) return false;
                            continue;
                        }
                        else
                        {
                            $is = copy($old.DIRECTORY_SEPARATOR.$filename,$new.DIRECTORY_SEPARATOR.$filename);
                        }
                    }
                }
            }
        }
        return $is ;
    }

    /// 获取目录下的所有文件路劲 包括子目录的文件
    function get_all_dir($dir) : array
    {
        $result = array();
        $handle = opendir($dir);
        if ( $handle )
        {
            while ( ( $file = readdir ( $handle ) ) !== false )
            {
                if ( $file != '.' && $file != '..')
                {
                    $cur_path = $dir.DIRECTORY_SEPARATOR.$file;
                    if ( is_dir ( $cur_path ) )
                    {
                        $files=$this->get_all_dir( $cur_path );
                        if($files) $result=$result?array_merge($result, $files):$files;
                    }
                    else
                    {
                        $result[] = $cur_path;
                    }
                }
            }
            closedir($handle);
        }
        return $result;
    }

    /// 判断文件或者文件夹是否可写
    /// @param $file string 文件或者文件夹路径
    function is_write($file): bool {
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
