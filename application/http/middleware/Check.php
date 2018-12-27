<?php

namespace app\http\middleware;
use \think\Request;

class Check
{
    // 空的中间件
    /// @return Response
    public function handle(Request $request, \Closure $next)
    {
      //echo ('middleware::Check');
      if('think' == $request->param('name'))
        return redirect('index/think');
      return $next($request);
    }
}
