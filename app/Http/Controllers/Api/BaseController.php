<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;


class BaseController extends Controller
{
    use Helpers;

    /****
     * BaseController constructor.
     */
    public function __construct()
    {

    }
}
