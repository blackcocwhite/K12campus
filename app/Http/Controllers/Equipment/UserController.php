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
use Uuid;

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
        ]);
        if ($validator->fails()) {
            return array('status' => 0, 'errmsg' => '缺失参数!');
        }
        if($auth = $this->check_accendant($input['mobile'])){
            $this->doRegister();
        }

        dd($auth);
    }

    protected function check_accendant($mobile){
        $status = 0;//标识维修商
        if (!$auth['repaire_id'] = DB::table('app_jiaozhuang_repaire')->where('repaire_phone', $mobile)->value('repaire_id')) {
            $status = 1;

            if (!$auth['repaire_id'] = DB::table('app_jiaozhuang_supply')->where('supply_mobile',  $mobile)->value('supply_id'))
                return false;
        }
        $auth['identity'] = $status;
        return $auth;
    }

    protected function doRegister(){
        $uid = Uuid::generate(1);
        $data['repaire_id'] = $_check['data']['repaire_id'];
        $data['repaire_user_id'] = $uid->string;
        $data['user_id'] = $_SERVER['HTTP_AUTHORIZATION'];
        $data['parent_id'] = '';
        $data['flag'] = 0;
        $data['create_time'] = Carbon::now();
        $data['identity'] = $_check['data']['identity'];
    }
}