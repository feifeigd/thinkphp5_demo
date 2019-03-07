<?php

namespace app\paydemo\controller;

use think\Controller;
use think\Request;

class Pay extends Controller
{
    private $pay_memberid = "10002";   //商户后台API管理获取
    private $md5key = "商户APIKEY";   //商户后台API管理获取
    private $tjurl = "http://www.daikuan2345.vip/Pay_Index.html";   //提交地址

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //return 'TODO';
        return $this->fetch('', [
            'pay_data' => [
                'pay_applydate'     => date('Y-m-d H:i:s'),   // 订单时间
                'pay_notifyurl'     => url('pay/notifyurl', '', true, true),        // 服务端返回地址
                'pay_callbackurl'   => url('pay/callbackurl', '', true, true),    // 页面跳转返回地址
            ],
            'tjurl'             => $this->tjurl,
        ]);
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

    public function callbackurl(){
        return '支付成功';
    }

    public function notifyurl(){

        return 'success';
    }

}
