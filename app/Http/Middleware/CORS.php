<?php

namespace App\Http\Middleware;

use Closure;

class CORS
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

//        $http_origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : false;
//        $allowed_origins = ['http://test.8dsun.com','https://open.weixin.qq.com'];
//        if(in_array($http_origin, $allowed_origins)) {
            $response = $next($request);
            $response->headers->add([
                'Access-Control-Allow-Origin'=>'*',
                'Access-Control-Allow-Methods'=>'POST,GET,OPTIONS,PUT,DELETE',
                'Access-Control-Allow-Headers' => 'Content-Type,Accept,Authorization,X-Requested-With',
                'Access-Control-Allow-Credentials' => 'true'
            ]);
            return $response;
//        }
    }
}
