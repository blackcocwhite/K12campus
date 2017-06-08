<?php

namespace App\Http\Middleware;

use Closure;
use Predis;

class WechatUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!empty($_SERVER['HTTP_AUTHORIZATION'])) {
            if (Predis::exists("user:$_SERVER[HTTP_AUTHORIZATION]")) {
                return $next($request);
            }
        }
        return response()->json(['status'=>0,'errmsg'=>'你没有权限']);
    }
}
