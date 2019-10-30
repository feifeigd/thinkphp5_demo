<?php
/**
 * Created by PhpStorm.
 * User: luofei
 * Date: 2019/5/4
 * Time: 2:29
 */

use files\File;

/*************************************************************
FF 将数组或者字符串写入到文件中
@param   filename string  文件路径
@param   data string|array   要写入文件的内容 可以为数组，如果数据为真表示写入，如果数据不为真表示读取
@param   path string   写入文件路径 默认为当前模块的路径
@param   type string   生成的格式 1 为[] 0为array();
@return  bool
 *************************************************************/
function FF($filename,$data="",$path='',$type = 1)
{
    if(!$path)
    {
        $path = env('app_path');

        $module = request()->module();

        if($module) $path = env('module_path'); // 当前模块目录
    }

    $F  = new File();

    $file = $path.$filename.".php";
    if($data=="*")
    {
        $F-> f_delete($file);
        return true;
    }

    if($data)
    {
        if(is_array($data) )
        {
            $data = var_export($data, true);
            if($type) $data = preg_replace(['/array \(/','/\)/i'],['[',']'],$data);
        }
        $F->write($file,'<?php '.PHP_EOL.'return "' . $data . '";');
    }

    if($F->f_has($file))
    {
        $config = include($file);
    }
    else
    {
        $config = false;
    }

    return $config;
}

/// 打印N个字符
function numStr($num,$str = '')
{
    $num = $num ? $num : 0;
    $str = $str ? $str : '&nbsp;';
    $returnData = "";
    for($i = 0 ; $i < $num ;$i++)
    {
        $returnData .= $str;
    }

    return $returnData;
}

/// 获取网站根目录
/// @return string
function getRoot(){
    $rootUrl = request()->rootUrl();
    $rootUrl = preg_replace(['/\/public/i'], [''], $rootUrl);
    return (trim($rootUrl) ?: '').'/';
}

/// 判断模块是否安装
/// @param sign string 模块目录名称
/// @return bool
function isInstall($sign){
    $F = new File();
    $installPath = env('app_path').$sign.DIRECTORY_SEPARATOR;
    return $F->f_has($installPath.'install.install');
}

/// 判断是否有模块
/// @param $sign string 模块目录名称
/// @return bool
function isModule($sign){
    $F = new File();
    $installPath = env('app_path').$sign.DIRECTORY_SEPARATOR;
    return $F->d_has($installPath);
}

/*************************************************************
http 请求
@param   url string   访问的URL
@param   post array   post数据(不填则为GET)
@param   cookie string   提交的$cookies
@param   returnCookie int   是否返回$cookies
@return  int
 *************************************************************/
function http_curl($url,$post='',$cookie='', $returnCookie=0){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
    curl_setopt($curl, CURLOPT_REFERER, $_SERVER['HTTP_HOST']);
    if($post) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    if($cookie) {
        curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    }
    curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($curl);

    if (curl_errno($curl)) {
        return curl_error($curl);
    }
    curl_close($curl);
    if($returnCookie){
        list($header, $body) = explode("\r\n\r\n", $data, 2);
        preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
        $info['cookie']  = substr($matches[1][0], 1);
        $info['content'] = $body;
        return $info;
    }else{
        return $data;
    }
}

/*************************************************************
array_del_key 删除数组key
@param   $array array   要操作的数组
@param   $key string|array   要删除的key
@return  array
 *************************************************************/
function array_del_key($array,$key){
    if(is_array($key))
    {
        foreach($key as $v)
        {
            if( isset($array[$v]) ) unset($array[$v]);
        }
    }
    else
    {
        if( isset($array[$key]) ) unset($array[$key]);
    }

    return $array;
}

/*************************************************************
getConfig 读取配置信息，支持跨模块读取
@param   $name string   要读取的配置参数
@param   $default string   默认值
@param   $module_name string   模块名称
@return  array
 *************************************************************/
function getConfig($name=NULL,$default="",$module_name=""){

    $config = NULL;
    if(!$module_name)
    {
        $config = config($name,$default);
    }
    else
    {
        if (!strpos($name, '.')) {
            $name = 'app.' . $name;
        } elseif ('.' == substr($name, -1)) {   // xx.
            $name = substr($name, 0, -1);
        }

        $name = explode(".",$name);
        $configFileName = strtolower($name[0]);
        $moduleConfigPath = env('app_path'). $module_name.DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR;

        $config[$configFileName] = FF($configFileName,NULL,$moduleConfigPath);
        if(!$config[$configFileName])
        {
            return $default;
        }
        foreach ($name as $val) {
            if (isset($config[$val]))
            {
                $config = $config[$val];
            } else {
                return $default;
            }
        }
    }

    return $config;
}

/*************************************************************
getFun 执行函数，支持跨模块执行
@param   $functionName string   执行函数的名称
@param   $param array   执行函数的参数
@param   $module_name string   模块名称
@return  array
 *************************************************************/
function exeFun($functionName,$param=[],$module_name=""){

    $returnData = NULL;

    if(!$module_name)
    {
        $returnData =call_user_func_array($functionName,$param);
    }
    else
    {
        if(!function_exists($functionName))
        {
            $moduleLangPath = env('app_path'). $module_name.DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.config('app.default_lang').".php";
            Lang::load($moduleLangPath);   // 加载模块语言包
            $moduleConfigPath = env('app_path'). $module_name.DIRECTORY_SEPARATOR."common.php";
            require_once($moduleConfigPath);    // 加载模块的全局函数
        }

        if(function_exists($functionName)) $returnData =call_user_func_array($functionName,$param);
    }

    return $returnData;
}

/*************************************************************
isMobile 判断客户端是否为手机
@return  bool
 *************************************************************/
function isMobile(){

    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) return true;
    // 如果via信息含有wap则一定是移动设备
    if (isset ($_SERVER['HTTP_VIA'])) return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;

    if (isset($_SERVER['HTTP_USER_AGENT']))
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $mobile_agents = ["240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte"];

        foreach ($mobile_agents as $device) {
            if(stristr($user_agent, $device)) return true;
        }
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT']))
    {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false)
            && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) return true;
    }
    return false;
}

/*************************************************************
isWeixin 判断是否在微信内置浏览器中打开
@return  bool
 *************************************************************/
function isWeixin(){
    if( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) return true;
    return false;
}
?>

