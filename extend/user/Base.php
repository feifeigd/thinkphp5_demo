<?php


namespace user;

use files\File;
use think\App;
use think\Controller;

class Base extends Controller
{
    private $auth = 'auth';
    private $check = 'check';
    private $F;
    private $login = null;  // 登录函数的名字
    private $userInfo = [];
    protected $user = 'member'; // 用户模块

    /// 检查会员权限问题，如果没登录，强制跳转到登录页面
    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->F = new File();

        $this->is_login();
    }

    private function is_login(){
        if(isInstall($this->user)){
            $userExecFile = env('app_path').$this->user.DIRECTORY_SEPARATOR.'exec'.DIRECTORY_SEPARATOR.'login.php';
            if($this->F->f_has($userExecFile)){
                include_once $userExecFile;
                $loginInfo = call_user_func($this->login);
                if(!$loginInfo){
                    if($this->request->isAjax() || $this->request->isPatch()){
                        $err = ['err'=>1, 'content'=>lang('not_login'), 'code'=>10001,];
                        echo json_encode($err);
                    }else {
                        echo "<script>top.location.href='".url($this->user.'/login/index')."'</script>";
                    }
                    exit();
                }
                $this->userInfo = $GLOBALS['userInfo'] = $loginInfo;
            }
            if (isInstall($this->auth)){
                $authExecFile = env('app_path').$this->auth.DIRECTORY_SEPARATOR.'exec'.DIRECTORY_SEPARATOR.'check.php';
                if($this->F->f_has($authExecFile)){
                    include_once $authExecFile;
                    $check = call_user_func_array($this->check, [$this->user]);
                    if (!$check){
                        if ($this->request->isAjax() || $this->request->isPatch()){
                            $err = ['err'=>1, 'content'=>lang('not_auth'), 'code'=>10002,];
                            echo json_encode($err);
                            exit();
                        }else{
                            $this->error(lang('not_auth'));
                        }
                    }
                }
            }
        }
    }
}
