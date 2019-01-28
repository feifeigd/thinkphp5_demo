<?php
/**
 * Created by PhpStorm.
 * User: luofei
 * Date: 2019/1/21
 * Time: 0:56
 */

namespace weixin;

require_once('config.php');

// TODO: 未完
class Wxapi
{
    var $appid = APPID;
    var $appsecret = APPSECRET;

    var $expires_time;
    var $access_token;

    // 构造函数，获取Access Token
    public function __construct($appid = NULL, $appsecret = NULL)
    {
        if($appid && $appsecret){
            $this->appid = $appid;
            $this->appsecret = $appsecret;
        }

        // 本地写入
        $res = file_get_contents('access_token.json');
        $result = json_decode($res, true);
        $this->expires_time = $result['expires_time'];
        $this->access_token = $result['access_token'];

        if(time() > ($this->expires_time + 3600)){
            $url = 'http://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->appid . '&secret=' . $this->appsecret;
            $res = $this->http_request($url);
            $result = json_decode($res, true);
            $this->access_token = $result['access_token'];
            $this->expires_time = time();
            file_put_contents('access_token.json', '{"access_token": "' . $this->access_token . '", "expires_time": ' . $this->expires_time . '}');
        }
    }

    // 测试接口，获取微信服务器ip地址
    public function get_callback_ip(){
        $url = 'https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token='. $this->access_token;
        $res = $this->http_request($url);
        return json_decode($res, true);
    }

    /// 获取用户信息
    public function get_user_info($openid){
        $url = 'http://api.weixin.qq.com/cgi-bin/user/info?access_token='. $this->access_token . '&openid=' . $openid . '&lang=zh_CN';
        $res = $this->http_request($url);
        return json_decode($res, true);
    }

    public function send_template_message($template){
        foreach ($template['data'] as $k => &$item)
            $item['value'] = urlencode($item['value']);
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $this->access_token;
        $res = $this->http_request($url, urldecode(json_encode($template)));
        return json_decode($res, true);
    }

    /// HTTP请求(支持HTTP/HTTPS，支持GET/POST)
    function http_request($url, $data = null){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if(!empty($data)){
            curl_setopt($curl, CURLOPT_PORT, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
}