<?php


namespace httpdown;


class Httpdown
{
    private $_speed;

    /// 下载
    /// @param $file 要下载的文件路径
    /// @param $name 文件名称，为空则与下载的文件名称一样
    /// $reload bool 是否开启断点续传
    public function download($file, $name = '', $reload = false){
        $fp = @fopen($file, 'rb');
        if(!fp)return '';
        if($name == '')
            $name = basename($file);
        $header_array = get_headers($file, true);

        $file_size = $header_array ? $header_array['Content-Length'] : filesize($file);

        $ranges = $this->getRange($file_size);
        $ua = $_SERVER['HTTP_USER_AGENT'];  // 判断是什么浏览器
        header('cache-control:public');
        header('content-type: application/octet-stream');

        $encoded_filename = urlencode($name);
        $encoded_filename = str_replace('+', '%20', $encoded_filename);

        // 解决下载文件名乱码
        if (preg_match('/MSIE/', $ua) || preg_match('/Trident/', $ua)){
            header('Content-Disposition: attachment; filename="'.$encoded_filename.'"');
        }elseif (preg_match('/Firfox/', $ua) ){
            header('Content-Disposition: attachment; filename*="utf-8\'\''.$name.'"');
        }elseif (preg_match('/Chrome/', $ua) ){
            header('Content-Disposition: attachment; filename="'.$encoded_filename.'"');
        }else {
            header('Content-Disposition: attachment; filename="'.$name.'"');
        }

        if ($reload && $ranges != null){    // 使用续传
            header('HTTP/1.1 206 Partial Content');
            header('Accept-Ranges: bytes');

            // 剩余长度
            header(sprintf('content-length:%u', $ranges['end'] - $ranges['start']));
            // range 信息
            header(sprintf('content-range:bytes %s-%s/%s', $ranges['start'], $ranges['end'], $file_size));
            fseek($fp, sprintf('%u', $ranges['start']));
        }else{
            header('HTTP/1.1 200 OK');
            header('content-length:'.$file_size);
        }
        /// 输出文件内容
        while (!feof($fp)){
            echo fread($fp, 4096);
            ob_end_flush();
        }
        ($fp != null) && fclose($fp);
    }

    /// 限制下载速度
    public function setSpeed($speed){
        if (is_numeric($speed) && $speed > 16 && $speed < 4096)
            $this->_speed = $speed;
    }

    private function getRange($file_size){
        if(isset($_SERVER['HTTP_RANGE']) && !empty($_SERVER['HTTP_RANGE'])){
            $range = $_SERVER['HTTP_RANGE'];
            $range = explode('-', substr($range, 6));
            if (count($range) < 2){
                $range[1] = $file_size;
            }
            $range = array_combine(['start', 'end'], $range);
            if (empty($range['start']))
                $range['start'] = 0;
            if (empty($range['end']))
                $range['end'] = $file_size;
            return $range;
        }
        return null;
    }
}
