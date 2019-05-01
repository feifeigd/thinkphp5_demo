<?php

namespace app\http\middleware;
use \think\Request;

/// 前置中间件
class Check
{
    // 空的中间件
    /// @return Response
    public function handle(Request $request, \Closure $next)
    {
      if('think' == $request->param('name'))
        return redirect('index/think');
      return $next($request);
    }
}
