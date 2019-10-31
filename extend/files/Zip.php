<?php


namespace files;


use think\Exception;
use ZipArchive;

class Zip
{
    private $F;
    private $zip;

    public function __construct()
    {
        $this->zip = new \ZipArchive();
        $this->F = new File();
    }

    /// 压缩单个文件
    public function add_file($filename, $filename_zip = null): bool {
        if ($filename_zip)
            return $this->zip->addFile($filename, $filename_zip);
        return $this->zip->addFile($filename);
    }
    /// 压缩整个目录
    public function zip($dir, $zipfilename = 'zip.zip'){
        $pathinfo = pathinfo($zipfilename);
        if($this->F->d_create($pathinfo['dirname'])){
            if($this->zip->open($zipfilename, \ZipArchive::CREATE) === true){   // ZipArchive::OVERWRITE 如果文件存在则覆盖
                $this->createZip($dir);
            }
            return $this->zip->close();
        }
    }

    /// 解压缩文件
    public function unzip($zipfilename = 'zip.zip', $path = ''): bool{
        if($this->zip->open($zipfilename) === true){
            $file_tmp = @fopen($zipfilename, 'rb');
            $bin = fread($file_tmp, 15);    // 只读15字节，各个不同文件类型， 头信息不同
            fclose($file_tmp);
            // 只针对zip的压缩包进行处理
            if(true === $this->getTypeList($bin)){
                $result = $this->zip->extractTo($path);
                $this->zip->close();
                return $result;
            }
        }
        return false;
    }

    /// 添加目录到zip对象
    public function createZip($dir, $parent = null):bool {
        $handle = opendir($dir);
        if($handle){
            try{
                while (($file = readdir($handle)) !== false){
                    if ($file != '.' && $file != '..'){
                        $cur_path = $dir.DIRECTORY_SEPARATOR.$file;
                        if(is_dir($cur_path)){
                            $parentParam = $parent ? $parent.'/'.$file : $file;
                            $this->createZip($cur_path, $parentParam);
                        }else {
                            $filename_zip = $parent ? $parent.'/'.$file : $file;
                            $this->add_file($cur_path, $filename_zip);
                        }
                    }
                }
                closedir($handle);
            }catch (Exception $e){
                return false;
            }
        }
    }

    /// 获取压缩文件的列表
    public function get_list($zipfilename = 'zip.zip'): bool {
        $file_dir_list = [];
        $file_list = [];
        if ($this->zip->open($zipfilename) == true){
            for ($i = 0; $i < $this->zip->numFiles; ++$i){
                $numfiles = $this->zip->getNameIndex($i);
                if (preg_match('/\/$/i', $numfiles))
                    $file_dir_list[] = $numfiles;   // 目录
                else
                    $file_list[] = $numfiles;   // 文件
            }
        }
        return ['files'=>$file_list, 'dirs'=>$file_dir_list];
    }

    /// 得到文件头与文件类型列表
    public function getTypeList($bin){
        return true;
    }
}
