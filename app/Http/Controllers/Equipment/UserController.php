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
use Illuminate\Database\QueryException;
use Predis;

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
     * @param Request $request
     * @param UserRepository $systemUser
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
        $return = $systemUser->systemRegister($input['openid'],$input['mobile'],$input['wappid']);
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
                if(!$this->doRegister($_info,$return['data']['_keys'][0])){
                    return array('stauts'=>0,'errmsg'=>'注册失败！');
                }
                $return['data']['repaire_id'] = $auth['repaire_id'];
                $return['data']['flag'] = $_info['flag'];
            }else{
                $return['data']['flag'] = 0;
            }

            $return['data']['mobile'] = $input['mobile'];
            return $return;
        }else{
            return $return;
        }
    }

    public function associateRepairer(Request $request, UserRepository $systemUser)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'displayName' => 'required',
            'mobile' => 'required',
            'openid' => 'required',
        ]);
        if ($validator->fails()) {
            return array('status' => 0, 'errmsg' => '缺失参数!');
        }

        $auth = $this->check_accendant($input['mobile']);
        if($auth){
            $mobile = Predis::hget("user:" . $_SERVER['HTTP_AUTHORIZATION'], 'mobile');
            $_info = array(
                'repaire_id' => $auth['repaire_id'],
                'user_id' => $_SERVER['HTTP_AUTHORIZATION'],
                'flag' => $auth['repaire_phone'] == $mobile ? 1 : 0,
                'parent_id' => '',
                'identity' => $auth['identity']
            );
            if(!$this->doRegister($_info)){
                return array('stauts'=>0,'errmsg'=>'注册失败！');
            }
            $systemUser->_modifyUser($input['openid'],'displayName',$input['displayName']);
            $return = array('status'=>1);
            $return['data']['userId'] = $_SERVER['HTTP_AUTHORIZATION'];
            $return['data']['repaire_id'] = $auth['repaire_id'];
            $return['data']['mobile'] = $input['mobile'];
            $return['data']['flag'] = $_info['flag'];
            return $return;
        }else{
            return array('status'=>0,'errmsg'=>'维修负责人关联失败！');
        }
    }
    /**
     * 检测是否为维修商负责人
     * @param $mobile
     * @return array|bool
     */
    protected function check_accendant($mobile){
        $status = 0;//标识维修商
        if ( !$auth = DB::table( 'app_jiaozhuang_repaire' )->where( 'repaire_phone', $mobile )->select( 'repaire_id', 'repaire_user_name as displayName', 'repaire_phone' )->first() ) {
            $status = 1;

            if ( !$auth = DB::table( 'app_jiaozhuang_supply' )->where( 'supply_mobile', $mobile )->select( 'supply_id as repaire_id', 'supply_user_name as displayName', 'supply_mobile as repaire_phone' )->first() )
                return false;
        }
        $array = [];
        foreach ($auth as $item) {
            $array['repaire_id'] = $item->repaire_id;
            $array['displayName'] = $item->displayName;
            $array['repaire_phone'] = $item->repaire_phone;
        }
        $array['identity'] = $status;
        return $array;
    }

    protected function doRegister(Array $arr,$_keys=''){
        if(User::where('user_id',$arr['user_id'])->first()){
            return true;
        }
        $uid = Uuid::generate(1);
        $arr['repaire_user_id'] = $uid->string;
        $arr['create_time'] = Carbon::now();
        try {
            User::create($arr);
        } catch(QueryException  $ex) {
            empty($_keys) or Predis::del($_keys);
            return false;
        }
        return true;
    }

    public function check_jyj($mobile)
    {
        $auth = DB::table( 'console_teacher_user' )->where( 'teacher_phone', $mobile )->select( 'user_id', 'teacher_name as displayName', 'teacher_phone' )->get();
        if ( !$auth ) {
            return array('status' => 0, 'errmsg' => '报修人员未登记！');
        }
        return array('status' => 1, 'data' => $auth);
    }
}
