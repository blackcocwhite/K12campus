<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;

class UserController extends Controller
{
    protected $user;
    public function __construct(UserRepository $user)
    {
        $this->user = $user;
    }

    /**
     * @param $openid
     * @return array
     */
    public function login($openid)
    {
        return $this->user->checkRegister($openid);
    }

    /**
     * @param $openid
     * @param $mobile
     * @return array
     */
    public function create($openid,$mobile,$wappid)
    {
        return $this->user->systemRegister($openid,$mobile,$wappid);
    }

}
