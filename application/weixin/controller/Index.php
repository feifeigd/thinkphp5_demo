<?php

namespace app\weixin\controller;

use think\Controller;
use think\Db;
use think\Request;

define('TOKEN', 'fangbei');

/// 微信消息接口实现
class Index extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        if(isset($_GET['echostr']))return $this->responseMsg();
        else return $this->valid();
    }

    /// 验证签名
    public function valid()
    {
        $echostr    = $_GET['echostr'];
        $signature  = $_GET['signature'];
        $timestamp  = $_GET['timestamp'];
        $nonce      = $_GET['nonce'];
        $token = TOKEN;
        $tmpArr = [$token, $timestamp, $nonce, ];
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if($tmpStr == $signature){
            echo $echostr;
            exit;
        }
    }

    /// 响应
    public function responseMsg()
    {
        $postStr = $GLOBALS['HTTP_RAW_POST_DATA'];
        if(empty($postStr)){
            echo '';
            exit;
        }
        $this->logger('R ' . $postStr);
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        if(($postObj->MsgType == 'event') && ($postObj->Event == 'subscribe' || $postObj->Event == 'unsubscribe' || $postObj->Event == 'TEMPLATESENDJOBFINISH')){
            // 过滤关注和取消关注事件
        }else{
            // 更新互动记录
            Db::name('user')->where('openid', strval($postObj->FromUserName))->setField('heartbeat', time());
        }
        $RX_TYPE = trim($postObj->MsgType);
        // 消息类型分离
        switch($RX_TYPE){
            case 'event':
                // TODO: Implement
                $result = $this->receiveEvent($postObj);
                break;
            case 'text':
                $result = $this->receiveText($postObj);
                break;
            default:
                $result = 'unknown msg type: ' . $RX_TYPE;
                break;
        }
        $this->logger('T ' . $result);
        echo $result;
    }

    /// 接收事件消息
    function receiveEvent($object){
        $weixin = new \weixin\Wxapi();
        $openid = strval($object->FromUserName);
        $content = '';
        switch($object->Event){
            case 'subscribe':
                $info = $weixin->get_user_info($openid);
                $municipalities = ['北京', '上海', '天津', '重庆', '香港', '澳门',];
                $sexes = ['', '男', '女', ];
                $data = [
                    'openid'    => $openid,
                    'nickname'  => str_replace("'", "", $info['nickname']),
                    'sex'       => $sexes[$info['sex']],
                    'country'   => $info['country'],
                    'province'  => $info['province'],
                    'city'      => in_array($info['province'], $municipalities) ? $info['province'] : $info['city'],
                    'scene'     => (isset($object->EventKey) && stripos(strval($object->EventKey), 'qrscene_'))
                        ? str_replace("qrscene_", "", $object->EventKey) : "0",
                    'headimgurl' => $info['headimgurl'],
                    'subscribe' => $info['subscribe_time'],
                    'heartbeat' => time(),
                    'remark'    => $info['remark'],
                    'score'     => 1,
                    'tagid'     => $info['tagid_list'],
                ];
                Db::name('user')->insert($data);
                $content = '欢迎关注' . $info['nickname'];
                break;
            case 'unsubscribe':
                Db::name('user')->where('openid', $openid)->delete();
                break;
            case 'CLICK':
                switch ($object->EventKey){
                    case 'TEXT':
                        $content = '微笑：/::)\n乒乓：/:oo\n中国：' . $this->bytes_to_emoji(0x1f1e8) . $this->bytes_to_emoji(0x1f1f3) . '\n仙人掌：' . $this->bytes_to_emoji(0x1f335);
                        break;
                    case 'SINGLENEWS':
                        $content = [
                            'Title' => '单图文标题',
                            'Description'   => '单图文内容',
                            'PicUrl'    => 'https://images2015.cnblogs.com/blog/340216/201605/340216-20160515215306820-740762359.jpg',
                            'Url'   => 'https://m.cnblogs.com/?u=txw1958',
                        ];
                        break;
                    case 'MULTINEWS':
                        $content = [];
                        $content[] = ["Title"=>"多图文1标题", "Description"=>"", "PicUrl"=>"http://images2015.cnblogs.com/blog/340216/201605/340216-20160515215306820-740762359.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958"];
                        $content[] = ["Title"=>"多图文2标题", "Description"=>"", "PicUrl"=>"http://d.hiphotos.bdimg.com/wisegame/pic/item/f3529822720e0cf3ac9f1ada0846f21fbe09aaa3.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958"];
                        $content[] = ["Title"=>"多图文3标题", "Description"=>"", "PicUrl"=>"http://g.hiphotos.bdimg.com/wisegame/pic/item/18cb0a46f21fbe090d338acc6a600c338644adfd.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958"];
                        break;
                    case 'MUSIC':
                        $content = ['Title'=>'最炫民族风', 'Description'=>'歌手：凤凰传奇', 'MusicUrl'=>"http://mascot-music.stor.sinaapp.com/zxmzf.mp3", "HQMusicUrl"=>"http://mascot-music.stor.sinaapp.com/zxmzf.mp3"];
                        break;
                    default:
                        $content = '点击菜单' . $object->EventKey;
                        break;
                }
                break;
            case 'VIEW':
                $content = '跳转链接 ' . $object->EventKey;
                break;
            case 'SCAN':
                $content = '扫描参数二维码，场景ID:' . $object->EventKey;
                break;
            case 'LOCATION':
                $content = '上传位置：纬度 ' . $object->Latitude . ';经度 ' . $object->Longitude;
                break;
            case 'scancode_waitmsg':
                if($object->ScanCodeInfo->ScanType == 'qrcode')$content = '扫码带提示：类型 二维码 结果: ' . $object->ScanCodeInfo->ScanResult;
                else if($object->ScanCodeInfo->ScanType == 'barcode'){
                    $code_info = explode(',', strval($object->ScanCodeInfo->ScanResult));
                    $codeValue = $code_info[1];
                    $content = '扫码带提示：类型 条形码 结果：' . $codeValue;
                }else $content = '扫码带提示：类型 ' . $object->ScanCodeInfo->ScanType . ' 结果： ' . $object->ScanCodeInfo->ScanResult;
                break;
            case 'scancode_push':
                $content = '扫码推事件';
                break;
            case 'pic_sysphoto':
                $content = '系统拍照';
                break;
            case 'pic_weixin':
                $content = '相册发图：数量 ' . $object->SendPicsInfo->Count;
                break;
            case 'pic_photo_or_album':
                $content = '拍照或者相册：数量 ' . $object->SendPicsInfo->Count;
                break;
            case 'location_select':
                $content = '发送位置：标签 ' . $object->SendLocationInfo->Label;
                break;
            default:
                $content = 'receive a new event: ' . $object->Event;
                break;
        }
        if(is_array($content)){
            if(isset($content[0]))
                $result = $this->transmitNews($object, $content);
            else if(isset($content['MusicUrl']))
                $result = $this->transmitMusic($object, $content);
        }
        else $result = $this->transmitText($object, $content);
        return $result;
    }

    /// 接收文本消息
    function receiveText($object){
        $keyword = trim($object->Content);
    }
    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
        return 'create';
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
        return null;
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
        return 'read';
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
        return null;
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
        return null;
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
        return null;
    }

    /// 日志记录
    function logger($log_content){
        if(isset($_SERVER['HTTP_APPNAME'])){    // SAE
            /*sae_set_display_errors(false);
            sae_debug($log_content);
            sae_set_display_errors(true);*/
        }else if($_SERVER['REMOTE_ADDR'] != '127.0.0.1'){
            $max_size = 1000000;
            $log_filename = 'log.xml';
            if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size))unlink($log_filename);
            file_put_contents($log_filename, date('H:i:s') . ' ' . $log_content . '\n', FILE_APPEND);
        }
    }
}
