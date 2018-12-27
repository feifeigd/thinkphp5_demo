<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 16:46
 */

namespace tests;


class EnvironmentTest extends TestCase
{

  public function testCacheIsOk(){
    cache('name','thinkphp');
    $cache = cache('name') ?:'no';
    $this->assertEquals('thinkphp', $cache, 'Cache Not Ok!');
  }

  public function testSessionIsOk(){
    session('name','thinkphp');
    $session = session('name') ?:'no';
    $this->assertEquals('thinkphp', $session, 'Session Not Ok!');
  }
}
