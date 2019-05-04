<?php
/**
 * Created by PhpStorm.
 * User: luofei
 * Date: 2019/5/4
 * Time: 2:51
 */

namespace app\install\controller;


use files\File;
use think\Controller;
use think\facade\Request;

class Index extends Controller
{
    public function index(){
        $root_path = env('root_path');
        $file = new File();
        if($file->f_has($root_path.'application/install/install.install')) die('程序已经安装，如重新安装请到install模块根目录下删除install.install');
        $step = Request::param('step') ?: 1;
        if(!session('step') || (session('step') + 1) < $step){
            session('step', $step);
            echo "<script>location.href='".url('install/index/index/', ['step'=>$step])."'</script>";
        }else
            session('step', $step);
        $config = config();
        switch ($step){
            case '1':   // 安装第一步,阅读安装协议
                $content = lang('Agreement');
                $this->assign('content', $content);
                $pagename = lang('step0');
                $this->assign('pagename', $pagename);
                return $this->fetch('install');
                break;
            case '2':   // 安装第二步,检测环境
                $pagename = lang('step1');
                $config = $config['config']['content']; // config.php.content
                $env_items = is_system($config['env_items']);
                $dir_items = is_write_array($config['dir_items']);
                $func_items = get_php_system($config['func_items']);
                $this->assign([
                    'pagename'      => $pagename,
                    'env_items'     => $env_items,
                    'dir_items'     => $dir_items,
                    'func_items'    => $func_items,
                ]);
                return $this->fetch('step1');
                break;
        }
        return $root_path;
    }

}