<?php


namespace form;


use think\App;
use user\Base;

class Form extends Base
{
    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->check();
    }

    /// 设置表单数据
    public function setFormInput(){
        $id = $this->request->param('id/d', 0);
        $sign = $this->request->param('sign');
        $type = isMobile() ? 'mobile' : 'pc';
        $form = exeFun('form', [$sign, $id, $type, true], 'field');
        $script = isset($form['script']) ? $this->display($form['script']) : '';
        $form = isset($form['form']) ? $form['form'] : [];

        $inputStr = '';
        foreach ($form as $k=>$v){
            $inputHtml = $this->display($v);
            $form[$k] = $inputHtml;
            $inputStr .= PHP_EOL.$inputHtml;
        }
        if ($form[$k])$form[$k] .= PHP_EOL.$script;
        $inputStr .= PHP_EOL.$script;
        $this->assign('form_group', $form);
        $this->assign('form', $inputStr);
    }

    /// 验证数据合法性
    public function check(){
        if(!isInstall('field')){
            if ($this->request->isAjax() || $this->request->isPatch()){
                return ['err'=>1, 'content'=>lang('no_install', ['module'=>'field'])];
            }else {
                $this->error(lang('no_install', ['module'=>'field']));
            }
        }
        $table = $this->request->param('table');
        if(!$table){
            if ($this->request->isAjax() || $this->request->isPatch()){
                return ['err'=>1, 'content'=>lang('err_param')];
            }else {
                $this->error(lang('err_param'));
            }
        }
        $this->assign('table', $table);
        $this->setFormInput();
    }
}
