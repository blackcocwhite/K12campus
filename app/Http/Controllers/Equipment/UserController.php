<?php
/**
 * Created by PhpStorm.
 * User: Jh
 * Date: 2017/3/29
 * Time: 15:37
 */

namespace App\Http\Controllers\Equipment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Equipment\User;
use DB;
use Validator;

class UserController extends Controller
{
    /**
     * 以user_id登录
     * @return array
     */
    public function login()
    {
        if($user = User::where('user_id',$_SERVER['HTTP_AUTHORIZATION'])->first()){
            return array('status'=>1,'data'=>$user);
        }else{
            return array('status'=>0);
        }
    }

    /**
     * 注册维修商负责人
     * @param Request $request
     * @return array
     */
    public function postRegister(Request $request){
        $input = $request->all();

        $validator = Validator::make($input, [
            'mobile' => 'required',
//            'displayName' => 'required'
        ]);
        if ($validator->fails()) {
            return array('status' => 0, 'errmsg' => '缺失参数!');
        }
        $status = 0;//标识维修商
        if (!$auth['repaire_id'] = DB::table('app_jiaozhuang_repaire')->where('repaire_phone', $input['mobile'])->value('repaire_id')) {
            $status = 1;

            if (!$auth['repaire_id'] = DB::table('app_jiaozhuang_supply')->where('supply_mobile',  $input['mobile'])->value('supply_id'))
                return array('status' => 0);
        }
        $auth['identity'] = $status;

        dd($auth);
    }
}