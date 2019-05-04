<?php
/**
 * Created by PhpStorm.
 * User: luofei
 * Date: 2019/5/4
 * Time: 12:17
 */

return [
    'content'   => [
        'env_items' => [
            // '检测环境变量名称' => ['说明文字', '需要值', '检测方法']
            // 检测方法返回数组 ['status' => 是否通过(boolean)， 'rightnow'=>当前状态显示]
            'os'    => ['操作系统', 'WINNT|Linux', function($system = 'WINNT|Linux'){
                $array['rightnow'] = PHP_OS;
                $system = explode('|', $system);
                if(!is_array($system)){
                    $array['status'] = (PHP_OS == $system);
                }else $array['status'] = in_array(PHP_OS, $system);
                return $array;
            }],
            'php'   => ['php版本', '5.6.0', function(){
                $array['rightnow'] = PHP_VERSION;
                $array['status'] = version_compare(PHP_VERSION, '5.6.0', '>=');
                return $array;
            }],
            'attachmentupload'  => ['附件上传', '可用', function(){
                if(@ini_get['file_uploads']){
                    $array['rightnow'] = ini_get('upload_max_filesize');
                    $array['status'] = true;
                }else{
                    $array['rightnow'] = '不可用';
                    $array['status'] = false;
                }
                return $array;
            }],
            'gdversion' => ['GD扩展', '可用', function(){
                if(extension_loaded('gd')){
                    $tmp = gd_info();
                    $array['rightnow'] = $tmp['GD Version'] ?: '';
                    $array['status'] = true;
                    unset($tmp);
                }else{
                    $array['rightnow'] = '不可用';
                    $array['status'] = false;
                }
                return $array;
            }],
        ],
        'dir_items'  => [
            'application/install',
        ],

        'func_items'    => [
            'file_get_contents' =>  'function',
            'curl_init' => 'function',
            'mb_strlen' => 'function',
            'mysqli'    => 'class',
        ],
    ],
];
