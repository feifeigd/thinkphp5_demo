<?php


namespace user;


use think\App;

class Admin extends Base
{
    public function __construct(App $app = null)
    {
        parent::__construct($app);

        $this->user = 'admin';
    }
}