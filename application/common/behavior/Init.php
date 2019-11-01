<?php


namespace app\common\behavior;

use app\admin\model\AdminModule as ModuleModel;
use think\facade\Request;

class Init
{
    private function check_environment(){
        header('Content-Type: text/html;charset=urt-8');
        if (version_compare(PHP_VERSION, '5.6.0', '<')) die('PHP版本过低，最少需要PHP5.6.0，请升级PHP版本!');
        // 检查是否安装
        if (!is_file(APP_PATH.'install/install.locl') && !is_file(APP_PATH.'install.lock')){
            if (!is_writable(APP_PATH.'runtime')){
                echo '请开启[runtime]文件夹的读写权限';
                exit;
            }
            define('BIND_MODULE', 'install');
        }
    }
    public function run(Request $request, &$params){
        $this->check_environment();

        define('IN_SYSTEM', true);
        // 安装操作直接return
        if (defined('BIND_MODULE'))return ;
        $_path = $request->path();
        $default_module = false;
        if ($_path != '/' && strtolower($_path) != 'index'){
            $_path = explode('/', $_path);
            if (isset($_path[0]) && !empty($_path[0])){
                if (is_dir('./application/'.$_path[0]) || $_path[0] == 'plugins'){
                    $default_module = true;
                    if ($_path[0] == 'plugins'){
                        define('BIND_MODULE', 'index');
                        define('PLUGIN_ENTRANCE', true);
                    }
                }
            }
        }

        if (!defined('PLUGIN_ENTRANCE') && !defined('CLOUD_ENTRANCE') && $default_module === false && !defined('BIND_MODULE')){
            // 设置前台默认模块
            $map = [
                'default'   => 1,
                'status'    => 2,
                'name'      => ['neq', 'admin',],
            ];
            $def_mod = ModuleModel::where($map)->value('name');
            if ($def_mod && !defined('ENTRANCE')){
                define('BIND_MODULE', $def_mod);
            }
        }
        // 后台强制关闭路由
        if (defined('ENTRANCE') && ENTRANCE == 'admin'){
            config('url_route_on', false);
            config('url_controller_layer', 'controller');
        }else {
            // 设置路由
            config('route_config_file', ModuleModel::moduleRoute());
        }
    }
}
