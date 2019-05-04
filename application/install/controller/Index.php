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
use think\Exception;
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
            case '3':   // 安装第三步，数据库信息配置
                if(!session('environment')) echo "<script>location.href='".url('index', ['step'=>2])."'</script>";
                $pagename = lang('step2');
                return $this->fetch('step2', ['pagename'=>$pagename]);
                break;
        }
        return $root_path;
    }

    /// ajax检测数据库是否连接
    public function AjaxCheckDatabase(){
        $config = Request::param();
        $configs = $this->ReturnSqlConfig($config); //dump($configs);die;
        $Sql = new \database\Model($configs);
        try{
            if($Sql->isconnect() == 1){
                if($configs['database']){
                    $dbname = $configs['database'];
                    if($Sql->has($dbname)){
                        echo 2;
                    }
                    else{
                        $res = $Sql->isconnect();
                        echo $res;
                    }
                }else{
                    $res = $Sql->isconnect();
                    echo $res;
                }
            }else echo 0;
        }catch (Exception $e){
            echo 0;
        }
    }

    /// 安装状态检测
    public function WebStatus(){
        session('environment', true);
    }

    /// 更新数据库配置内容，返回与tp要求格式相同的内容
    private function ReturnSqlConfig($config){
        $configs['type'] = isset($config['databassetype']) ? $config['databassetype'] : 'mysql';
        $configs['hostname'] = isset($config['databaseurl']) ? $config['databaseurl'] : '';
        $configs['database'] = isset($config['databasename']) ? $config['databasename'] : '';
        $configs['username'] = isset($config['databaseuser']) ? $config['databaseuser'] : '';
        $configs['password'] = isset($config['databasepassword']) ? $config['databasepassword'] : '';
        $configs['hostport'] = isset($config['databaseport']) ? $config['databaseport'] : '3306';
        $configs['params'] = [];
        $configs['charset'] = 'utf8mb4';
        $configs['prefix'] = isset($config['databaseprefix']) ? $config['databaseprefix'] : '';

        return $configs;
    }
}