<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Validator;

class UserController extends Controller
{
    protected $user;
    public function __construct(UserRepository $user)
    {
        $this->user = $user;
    }

    /**
     * @param $openid
     * @param $unionid
     * @param $wappid
     * @return array
     */
    public function login($openid,$unionid,$wappid)
    {
        return $this->user->checkRegister($openid,$unionid,$wappid);
    }

    /**
     * @param $request
     * @return array
     */
    public function create(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'uid' => 'required',
            'wappid' => 'required',
            'openid' => 'required',
            'mobile' => 'required',
        ]);
        if ($validator->fails()) {
            return array('status' => 0, 'errmsg' => '缺失参数!');
        }
        return $this->user->systemRegister($input['uid'],$input['openid'],$input['mobile'],$input['wappid']);
    }

}
