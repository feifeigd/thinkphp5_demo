<?php
/**
 * Created by PhpStorm.
 * User: luofei
 * Date: 2019/5/4
 * Time: 1:42
 */

namespace init;

use files\File;
use think\App;
use think\Controller;
use think\facade\Lang;
use think\facade\Url;

class Init extends Controller
{
    private $F;

    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->F = new File();
    }

    /// init 应用初始化执行代码
    public function appInit(){
        Lang::load(env('app_path').'common'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.config('app.default_lang').'.php');
        $show_index_type = config('cow.cow_show_index_type');
        // url 生成的连接方式 1:[/a]/public/index.php/b/c, 2:[/a]/public/b/c 3:/index.php/b/c 4: /b/c
        switch ($show_index_type){
            case 1:
                Url::root(request()->baseFile());
                break;
            case 2:
                Url::root(request()->rootUrl());
                break;
            default:
                if($show_index_type)
                    Url::root($show_index_type);
                else
                    Url::root(request()->baseFile());
        }

        // 自动加载common公共目录下的程序
        $commonPath = config('cow.cow_common_path') ?: env('app_path').'common/';
        $common = config('cow.cow_common') ?: [];
        foreach ($common as $v){
            $commonFilePath = $commonPath.$v.'.php';
            if($this->F->f_has($commonFilePath))
                include_once $commonFilePath;
        }
    }

    /// init 模块初始化执行代码
    public function moduleInit(){
        /*$show_index_type = config('cow.cow_show_index_type');

        // 判断是否有安装目录，和是否安装程序
        $installPath = env('app_path').'install/';
        $installOk = $installPath.'install.install';*/

        $module = request()->module();
        $domain = request()->domain();
        $port = request()->port();
        $url = $port != '80' ? $domain.':'.$port : $domain;
        if(isModule('install') && !isInstall('install') && $module != 'install'){
            $url .= url('install/Index/index'); // 跳转到安装页面
            header("Location:$url");
            exit();
        }
    }
}
