<?php
namespace App\Http\Controllers\Api;
use Predis;

class TestsController extends BaseController
{
    public function index()
    {
        $user = Predis::get('_app_wechat_accesstoken_e4e4e08355721e0201557225d4170000');
         return $this->response->array($user->toArray());
        // return $user;
        // return "['status'=>'success',''=>'message'=>'操作成功']";　
            // dd($user);
        // return $this->response->array($user->toArray());
    }
}
