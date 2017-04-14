<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Predis;

class UserController extends Controller
{
    protected $user;
    public function __construct(UserRepository $user)
    {
        $this->user = $user;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function login($openid)
    {
        return $this->user->checkRegister($openid);
    }

    /**
     * @param Request $request
     */
    public function create($openid,$mobile)
    {
       return $this->user->systemRegister($openid,$mobile);
    }

}
