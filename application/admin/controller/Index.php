<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;

class Index extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
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
    }

    public function main(){
        $info = [
            '操作系统'  => PHP_OS,
            '运行环境'  => $_SERVER["SERVER_SOFTWARE"],
            'PHP运行方式'   => php_sapi_name(),
            'PHP版本' => PHP_VERSION,
            // 'MySQL'  => $mysql,
            //'ThinkPHP版本'    => THINK_VERSION,
            '上传附件限制'    => ini_get('upload_max_filesize'),
            '执行时间限制'    => ini_get('max_execution_time').'秒',
            '服务器时间' => date('Y年n月j日 H:i:s'),
            // '北京时间'=>gmdate("Y年n月j日 H:i:s",time()+8*3600),
            '服务器域名' => $_SERVER['SERVER_NAME'],
            '服务器ip' => gethostbyname($_SERVER['SERVER_NAME']),
            '剩余空间'  => round((@disk_free_space('.') / (1024 * 1024)), 2) . 'M',
        ];
        $this->assign('info', $info);
        return $this->fetch();
    }
}
