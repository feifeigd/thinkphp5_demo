<?php

namespace app\file\controller;

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
        return $this->fetch();
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

    /// 上传单文件
    public function upload(){

        // 获取表单上传文件 例如上传了 001.jpg
        $file = request()->file('image'); // 根据html的form的字段名获取文件
        // 移动到框架应用根目录/uploads/目录下
        $info = $file
            ->rule('md5')   // md5的前两个字符作为目录名,剩余的作为文件名
            ->validate(['size'=>915678, 'ext'=>'jpg,png,gif', ])    // 上传验证
            ->move('../uploads');
        if($info){
            // 成功上传后,获取上传信息
            // 输出jpg
            echo "扩展名,不带点={$info->getExtension()}<br>";
            // 输出 oo/hashxx.jpg
            echo "文件路径={$info->getSaveName()}<br>";
            // 输出文件名 hashxx.jpg
            echo "文件名={$info->getFilename()}<br>";
            echo "文件md5={$info->md5()}<br>";
        }else{
            // 上传失败获取错误
            echo $file->getError();
        }
    }
    /// 上传多文件
    public function uploads(){

        // 获取表单上传文件 例如上传了 001.jpg
        $files = request()->file('image'); // 根据html的form的字段名获取文件
        foreach($files as $file){
            // 移动到框架应用根目录/uploads/目录下
            $info = $file->move('../uploads');
            if($info){
                // 成功上传后,获取上传信息
                // 输出jpg
                echo "扩展名,不带点={$info->getExtension()}<br>";
                // 输出 oo/hashxx.jpg
                echo "文件路径={$info->getSaveName()}<br>";
                // 输出文件名 hashxx.jpg
                echo "文件名={$info->getFilename()}<br>";
                echo "文件md5={$info->md5()}<br>";
            }else{
                // 上传失败获取错误
                echo $file->getError();
            }
        }
    }
}
