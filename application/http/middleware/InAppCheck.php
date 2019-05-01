<?php

namespace app\http\middleware;

/// 前置中间件
/// 访问环境检查,是否是微信或支付宝等
class InAppCheck
{
    public function handle($request, \Closure $next)
    {
      if(preg_match('~micromessenger~i', $request->header('user-agent')))
        $request->InApp = 'WeChat'; // 中间件向控制器传参
      if(preg_match('~alipay~i', $request->header('user-agent')))
        $request->InApp = 'Alipay'; // 中间件向控制器传参
      return $next($request);
    }
}
