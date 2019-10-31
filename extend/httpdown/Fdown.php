<?php

namespace httpdown;

use files\File;
use files\Zip;
use think\Exception;

/// 从别的服务器，下载文件到本服务器
class Fdown
{
    private $F;
    private $savefile;
    private $url;
    private $Z;

    public function __construct($url = '', $savefile = '')
    {
        $this->savefile = $savefile;
        $this->url = $url;

        $this->F = new File();
        $this->Z = new Zip();
    }

    /// 下载文件
    /// @return int 0失败,1成功,-1解压失败
    public function down($url = '', $savefile = '', $zippath = null): int{
        ob_clean();
        ob_end_flush();
        set_time_limit(0);

        $savefile = $savefile ?: $this->savefile;
        $url = $url ?: $this->url;
        if (!$savefile || !$url) return -2;

        try{
            $header_array = get_headers($url, true);
            $size = $header_array['Content-Length'];
            if ($size <= 0)return -3;
            $pathinfo = pathinfo($savefile);
            $this->F->d_create($pathinfo['dirname']);
            if (!isset($pathinfo['extension'])){
                $pathinfo_url = pathinfo($url);
                $savefile .= $pathinfo_url['basename'];
            }
            $fp_output = fopen($savefile, 'w');
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_FILE, $fp_output);
            curl_exec($ch);
            curl_close($ch);

            if ($zippath){
                return $this->Z->unzip($savefile, $zippath) ? 1 : -1;
            }
            return 1;
        }catch (Exception $e){
            return 0;
        }
        return 0;
    }
}
