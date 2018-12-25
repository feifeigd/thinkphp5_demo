<?php

namespace app\api\controller;

use think\Controller;
use think\Request;

/// swagger: 登陆相关
class Passport extends Controller
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

    /**
     * post: 发送验证码
     * path: sendVerify/phone/{phone}/device_type/{deviceType}
     * @param: phone - {string} 手机号
     * @param: deviceType - {int} = [0|1|2|3|4] 设备类型(0: android手机, 1: ios手机, 2: android平板, 3: ios平板, 4: pc)
     */
    public function sendVerify($phone, $deviceType){
        return [
            'code'  => 200,
            'message'   => '发送验证码',
            'data'  => [
                'phone' => $phone,
                'deviceType'    => $deviceType,
            ],
        ];
    }

    /**
     * post: 登陆
     * path: login
     * @param: phone - {string} 手机号
     * param: password - {string} 密码
     * param: deviceType - {int} = [0|1|2|3|4] 设备类型(0:android手机,1:ios手机,2:android平板,3:ios平板,4:pc)
     * param: verifyCode - {string} = 0 验证码
     */
    public function login($phone, $password, $deviceType, $verifyCode = '0'){
        return [
            'code'  => 200,
            'message'   => '登陆成功',
            'data'  => [
                'phone' => $phone,
                'password'  => $password,
                'deviceType'    => $deviceType,
                'verifyCode'    => $verifyCode,
            ],
        ];
    }

    /**
     * get: 获取配置
     * path: profile
     * param: keys - {string[]} 需要获取配置的key值数组
     */
    public function profile($keys){
        return [
            'code'  => 200,
            'message'   => '获取成功', 
            'data'  => $keys,
        ];
    }
}
