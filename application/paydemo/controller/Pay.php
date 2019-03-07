<?php

namespace app\paydemo\controller;

use think\Controller;
use think\facade\Request;

class Pay extends Controller
{
    // 商户后台->API管理->API开发文档 获取
    private $pay_memberid = "10030";
    private $md5key = "9cqosyjps6etvl2xa8ofykh77iztwgyd";   //商户后台API管理获取
    private $tjurl = "http://www.daikuan2345.vip/Pay_Index.html";   //平台网关地址 提交地址

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //return 'TODO';
        // doc http://www.daikuan2345.vip/Home_Index_document.html
        // 签名字段
        $pay_data = [
            'pay_memberid'      => $this->pay_memberid,
            'pay_orderid'       => Request::post('pay_orderid/s'),
            'pay_applydate'     => date('Y-m-d H:i:s'),   // 订单时间
            'pay_bankcode'      => Request::post('pay_bankcode/s'),
            'pay_notifyurl'     => url('pay/notifyurl', '', true, true),        // 服务端返回地址
            'pay_callbackurl'   => url('pay/callbackurl', '', true, true),    // 页面跳转返回地址
            'pay_amount'        => Request::post('pay_amount/f'),
        ];
        ksort($pay_data);
        $md5str = "";
        foreach ($pay_data as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }

        $sign = strtoupper(md5($md5str . "key=" . $this->md5key));
        // 无需签名的字段
        $pay_data['pay_md5sign'] = $sign;
        $pay_data['pay_productname']    = Request::port('pay_productname');
        // 非必填字段
        $pay_data['pay_productnum']     = Request::port('pay_productnum');
        $pay_data['pay_attach']         = Request::port('pay_attach');
        $pay_data['pay_productdesc']    = Request::port('pay_productdesc');
        $pay_data['pay_producturl']     = Request::port('pay_attach');

        return $this->fetch('', [
            'pay_data' => $pay_data,
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

    // 同步通知
    public function callbackurl(){
        return '支付成功';
    }

    // 异步通知
    public function notifyurl(){

        return 'OK';    // 给平台返回成功
    }

}
