<?php

namespace app\http\middleware;

class Hello
{
    public function handle($request, \Closure $next)
    {
        $request->hello = "ThinkPHP";   // 中间件向控制器传参
        return $next($request);
    }
}
