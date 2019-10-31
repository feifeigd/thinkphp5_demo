<?php


namespace httpdown;

use think\Exception;

/// 从别的服务器，下载文件到本服务器
class Filedown
{
    static function get($url, $save_file, $speed =  10240, $headers = [], $timeout = 10){
        set_time_limit(0);
        $url_info = self::parse_url($url);
        if (!$url_info['host']){
            throw new Exception('Url is Invalid');
        }

        // default header
        $def_headers = [
            'Accept'    => '*/*',
            'User-Agent'    => 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0)',
            'Accept-Encoding'   => 'gzip, deflate',
            'Host'              => $url_info['host'],
            'Connection'        => 'Close',
            'Accept-Language'   => 'zh-cn',
        ];
        // merge header
        $headers = array_merge($def_headers, $headers);
        // get content length
        $content_length = self::get_content_size($url_info['host'], $url_info['port'], $url_info['request'], $headers, $timeout);

        if(!$content_length)
            throw new Exception('Content-Length is Not Exists');
        // get exists length
        $exists_length = is_file($save_file) ? filesize($save_file): 0;
        $data_file = $save_file.'.data';
        $exists_data = is_file($data_file) ? json_decode(file_get_contents($data_file), 1) : ['length'=>0];
        if ($exists_length == $content_length){
            $exists_data && @unlink($data_file);
            return true;
        }

        if ($exists_data['length'] != $content_length || $exists_length > $content_length){
            $exists_data = ['length'=>$content_length];
        }
        file_put_contents($data_file, json_encode($exists_data));
        try{
            $download_status = self::download_content($url_info['host'], $url_info['port'], $url_info['request'], $save_file, $content_length, $exists_length, $speed, $headers, $timeout);
            if ($download_status)
                @unlink($data_file);
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }
        return true;
    }

    static function parse_url($url){
        $url_info = self::parse_url($url);
        if(!$url_info['host'])
            return false;
        if (!isset($url_info['port']))
            $url_info['port'] = 80;
        if (!isset($url_info['query']))
            $url_info['query'] = '';
        $url_info['request'] = $url_info['path'] . $url_info['query'];
        return $url_info;
    }

    /// download content by chunk
    static function download_content($host, $port, $url_path, $save_file, $content_length, $range_start, $speed, &$headers, $timeout){
        $request = self::build_header('GET', $url_path, $headers, $range_start);
        $fsocket = @fsockopen($host, $port, $errno, $errstr, $timeout);
        stream_set_blocking($fsocket, true);
        stream_set_timeout($fsocket, $timeout);
        fwrite($fsocket, $request);
        $status = stream_get_meta_data($fsocket);
        if ($status['timed_out']){
            throw new Exception('Socket Connect Timeout');
        }
        $is_header_end = 0;
        $total_size = $range_start;
        $file_fp = fopen($save_file, 'a+');
        while (!feof($fsocket)){
            if (!$is_header_end){
                $line = @fgets($fsocket);
                if (in_array($line, ['\n', '\r\n'])){
                    $is_header_end = 1;
                }
                continue;
            }
            $resp = fread($fsocket, $speed);
            $read_length = strlen($resp);
            if ($resp === false || $content_length < $total_size + $read_length){
                fclose($fsocket);
                fclose($file_fp);
                throw new Exception('Socket I/O Error Or File Was Changed');
            }
            $total_size += $read_length;
            fputs($file_fp, $resp);
            if ($content_length == $total_size)
                break;
            sleep(1);
        }
        fclose($fsocket);
        fclose($file_fp);
        return true;
    }

    static function get_content_size($host, $port, $url_path, &$headers, $timeout){
        $request = self::build_header('HEAD', $url_path, $headers);
        $fsocket = @fsockopen($host, $port, $errno, $errstr, $timeout);
        stream_set_blocking($fsocket, true);
        stream_set_timeout($fsocket, $timeout);
        fwrite($fsocket, $request);
        $status = stream_get_meta_data($fsocket);
        if ($status['timed_out'])
            return 0;
        $length = 0;
        while (!feof($fsocket)){
            $line = @fgets($fsocket);
            if (in_array($line, ['\n', '\r\n']))
                break;
            $line = strtolower($line);
            if (substr($line, 0, 9) == 'location:'){    // 重定向了？
                $location = trim(substr($line, 9));
                $url_info = self::parse_url($location);
                if (!$url_info['host']){
                    return 0;
                }
                fclose($fsocket);
                return self::get_content_size($url_info['host'], $url_info['port'], $url_info['request'], $headers, $timeout);
            }
            if (strpos($line, 'content-length:') !== false){
                list(, $length) = explode('content-length:', $line);
                $length = (int)trim($length);
            }
        }
        fclose($fsocket);
        return $length;
    }

    static function build_header($action, $url_path, &$headers, $range_start = -1){
        $out = $action . " {$url_path} HTTP/1.0\r\n";
        foreach ($headers as $hkey=>$hval){
            $out .= $hkey.': ' . $hval. '\r\n';
        }
        if ($range_start > -1){
            $out .= 'Accept-Ranges: bytes\r\n';
            $out .= "Range: bytes={$range_start}-\r\n";
        }
        $out .= '\r\n';
        return $out;
    }
}
