<?php
namespace app\api\controller;

use think\Controller;
use \OpenApi;

class Index extends Controller
{
    public function index()
    {
      $this->redirect('/api.php');
    }
}
