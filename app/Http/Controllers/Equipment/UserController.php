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
use App\Repositories\UserRepository;
use DB;
use Validator;
use Uuid;
use Carbon\Carbon;
use Predis;
use Illuminate\Database\QueryException;

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
     * 注册并直接写入维修负责人
     * @param Request $request openid mobile
     * @return array
     */
    public function postRegister(Request $request,UserRepository $systemUser){
        $input = $request->all();

        $validator = Validator::make($input, [
            'openid' => 'required',
            'mobile' => 'required',
        ]);
        if ($validator->fails()) {
            return array('status' => 0, 'errmsg' => '缺失参数!');
        }
        $return = $systemUser->systemRegister($input['openid'],$input['mobile']);
        if($return['status']){
            if($auth = $this->check_accendant($input['mobile'])){
                $systemUser->_modifyUser($input['openid'],'displayName',$auth['displayName']);
                $_info = array(
                    'repaire_id' => $auth['repaire_id'],
                    'user_id' => $return['data']['userId'],
                    'flag' => 1,
                    'parent_id' => $return['data']['userId'],
                    'identity' => $auth['identity']
                );
                if(!$this->doRegister($_info,"wechat.user:".$input['openid'])){
                    return array('stauts'=>0,'errmsg'=>'注册失败！');
                }
            }
            $return['data']['repaire_id'] = $auth['repaire_id'];
            $return['data']['mobile'] = $input['mobile'];
            return $return;
        }else{
            return $return;
        }
    }

    /**
     * 检测是否为维修商负责人
     * @param $mobile
     * @return array|bool
     */
    protected function check_accendant($mobile){
        $status = 0;//标识维修商
        if (!$auth = DB::table('app_jiaozhuang_repaire')->where('repaire_phone', $mobile)->select('repaire_id','repaire_user_name as displayName')->get()) {
            $status = 1;

            if (!$auth = DB::table('app_jiaozhuang_supply')->where('supply_mobile',  $mobile)->select('supply_id as repaire_id','supply_user_name as displayName')->get())
                return false;
        }
        $array = [];
        foreach ($auth as $item) {
            $array['repaire_id'] = $item->repaire_id;
            $array['displayName'] = $item->displayName;
        }
        $array['identity'] = $status;
        return $array;
    }

    protected function doRegister(Array $arr,$_keys){
        $uid = Uuid::generate(1);
        $arr['repaire_user_id'] = $uid->string;
        $arr['create_time'] = Carbon::now();
        try {
            User::create($arr);
        } catch(QueryException  $ex) {
            Predis::del($_keys);
            return false;
        }
        return true;
    }
}