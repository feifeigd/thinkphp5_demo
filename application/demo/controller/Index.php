<?php
namespace app\demo\controller;

use think\Controller;
use think\facade\Request;

class Index extends Controller
{
    public function index()
    {
        $this->assign('demo_time', Request::time());
        return $this->fetch();
    }
}
