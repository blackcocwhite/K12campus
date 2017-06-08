<?php

namespace App\Http\Middleware;

use Closure;
use Predis;
use DB;

class EquipmentAuth
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
            if ($user_id = Predis::hget("user:$_SERVER[HTTP_AUTHORIZATION]",'userId')) {
                if(DB::table('app_jiaozhuang_repaire_user')->where('user_id',$user_id)->first()){
                    return $next($request);
                }
            }
        }
        return response()->json(['status'=>0,'errmsg'=>'您没有权限！']);
    }
}
