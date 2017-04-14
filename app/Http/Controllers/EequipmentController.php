<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Predis;
use DB;
use Uuid;
use Validator;
use Carbon\Carbon;

class EequipmentController extends Controller
{
    public function login($userid)
    {
        if ($user = DB::table('app_jiaozhuang_repaire_user')->where('user_id', $userid)->first()) {
            return array('status' => 1, 'data' => $user);
        } else {
            return array('status' => 0);//跳转去验证负责人手机号
        }
    }

    /*验证负责人手机   login失败跳转过来 */
    public function checkAccendant($userid, $mobile)
    {
        $_check = $this->_checkPrincipal($mobile);
        if ($_check['status'] === 0) {
            return array('status' => 0);
        } else {
            if(DB::table('app_jiaozhuang_repaire_user')->where('user_id',$userid)->first()){
                return array('status'=>1,'errmsg'=>'您已经验证过！');
            }
            $uid = Uuid::generate(1);
            $data['repaire_id'] = $_check['data']['repaire_id'];
            $data['repaire_user_id'] = $uid->string;
            $data['user_id'] = $userid;
            $data['parent_id'] = '';
            $data['flag'] = 0;
            $data['create_time'] = Carbon::now();
            $data['identity'] = $_check['data']['identity'];

            if (DB::table('app_jiaozhuang_repaire_user')->insert($data)) {
                return array('status' => 1, 'data' => $data);
            }
            return array('status' => 0);
        }
    }

    /*验证注册用户是否为负责人 如果不是则跳转输入手机验证负责人*/
    public function checkHead($userid,$mobile)
    {
        $_check = $this->_checkPrincipal($mobile);
        if ($_check['status'] === 0) {
            return array('status' => 0);
        } else {

            $uid = Uuid::generate(1);
            $data['repaire_id'] = $_check['data']['repaire_id'];
            $data['repaire_user_id'] = $uid->string;
            $data['user_id'] = $userid;
            $data['parent_id'] = $userid;
            $data['flag'] = 1;
            $data['create_time'] = Carbon::now();
            $data['identity'] = $_check['data']['identity'];

            if (DB::table('app_jiaozhuang_repaire_user')->insert($data)) {
                return array('status' => 1, 'data' => $data);
            }
            return array('status' => 0);
        }
    }

    public function _checkPrincipal($mobile)
    {
        $status = 0;//标识维修商
        $auth['repaire_id'] = DB::table('app_jiaozhuang_repaire')->where('repaire_phone', $mobile)->value('repaire_id');
        if (is_null($auth['repaire_id'])) {
            $status = 1;
            $auth['repaire_id'] = DB::table('app_jiaozhuang_supply')->where('supply_mobile', $mobile)->value('supply_id');
            if (is_null($auth['repaire_id'])) return array('status' => 0);
        }
        $auth['identity'] = $status;
        return array('status' => 1, 'data' => $auth);
    }

    /*待处理订单*/
    //2c90f8225ad6b399015ad6c4ad7a0007
    public function pendingOrder($userid)
    {
        $repaire_id = DB::table('app_jiaozhuang_repaire_user')->where('user_id', '=', $userid)->value('repaire_id');
        if (is_null($repaire_id)) {
            return array('status' => 0, 'errmsg' => '没有数据');
        }
        $data = DB::table('app_jiaozhuang_order')
            ->join('app_jiaozhuang_channel_equipment', 'app_jiaozhuang_order.channel_equipment_id', '=', 'app_jiaozhuang_channel_equipment.channel_equipment_id')
            ->where('app_jiaozhuang_channel_equipment.repair_id', '=', $repaire_id)
            ->where('app_jiaozhuang_order.state', '=', '2')
            ->where('app_jiaozhuang_order.receive_status', '=', '0')
            ->orderBy('repaire_time')
            ->get();
        return array('status' => 1, 'data' => $data);
    }

    public function handingOrder($user_id)
    {
        $data = DB::table('app_jiaozhuang_order')
            ->where('receive_user_id', '=', $user_id)
            ->where('state', '=', '2')
            ->where('receive_status', '=', '1')
            ->orderBy('repaire_time')
            ->get();
        if(empty($data)){
            return array('status' => 0, 'errmsg' => '没有数据');
        }else{
            return  array('status' => 1, 'data' => $data);
        }
    }

    public function completeOrder($user_id)
    {
        $data = DB::table('app_jiaozhuang_order')
            ->where('receive_user_id', '=', $user_id)
            ->where('state', '=', '3')
            ->where('receive_status', '=', '1')
            ->orderBy('repaire_time')
            ->get();
        if(empty($data)){
            return array('status' => 0, 'errmsg' => '没有数据');
        }else{
            return array('status' => 1, 'data' => $data);
        }
    }

    public function evaluatedOrder($user_id)
    {
        $data = DB::table('app_jiaozhuang_order')
            ->where('receive_user_id', '=', $user_id)
            ->where('state', '=', '4')
            ->where('receive_status', '=', '1')
            ->orderBy('repaire_time')
            ->get();
        if(empty($data)){
            return array('status' => 0, 'errmsg' => '没有数据');
        }else{
            return  array('status' => 1, 'data' => $data);
        }
    }
    /*
     * @param  order_id user_id repaire_id place
     * */
    public function receiveOrder(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'user_id' => 'required',
            'order_id' => 'required',
            'repaire_id' => 'required',
            'place' => 'required'
        ]);

        if ($validator->fails()) {
            return array('status' => 0, 'errmsg' => '缺失参数!');
        }

        if (DB::table('app_jiaozhuang_order')->where('order_id', '=', $input['order_id'])->value('receive_status') === 0) {
            $now = Carbon::now();
            if($user = DB::table('app_jiaozhuang_repaire_user')->where('user_id',$input['user_id'])->first()){
                if($user->repaire_id != $input['repaire_id']){
                    return array('status'=>0,'errmsg'=>'维修商不匹配');
                }
                DB::beginTransaction();
                $res1 = DB::table('app_jiaozhuang_order')
                    ->where('order_id', '=', $input['order_id'])
                    ->update([
                        'receive_user_id' => $input['user_id'],
                        'receive_status' => 1,
                        'create_time'=>$now,
                        'receive_time' => $now
                    ]);
                $uuid = Uuid::generate(1);
                $array = array(
                    'order_id' => $input['order_id'],
                    'schedule_name' => '接收工单',
                    'create_time' => $now,
                    'place' => $input['place'],
                    'user_id' => $input['user_id'],
                    'schedule_id' => $uuid->string,
                    'status' => 2,
                    'repaire_id' => $input['repaire_id']
                );
                $res2 = DB::table('app_jiaozhuang_schedule')->insert($array);
                if ($res1 === 0 || $res2 == false) {
                    DB::rollBack();
                    return array('status' => 0, 'errmsg' => '接单失败！');
                } else {
                    DB::commit();
                    return array('status' => 1);
                }
            }
        } else {
            return array('status' => 0, 'errmsg' => '工单已经处理中...');
        }
    }

    /*确认上门*/
    public function confirmVisit(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'user_id' => 'required',
            'order_id' => 'required',
            'repaire_id' => 'required',
            'place' => 'required'
        ]);

        if ($validator->fails()) {
            return array('status' => 0, 'errmsg' => '缺失参数!');
        }
        if ($order = DB::table('app_jiaozhuang_order')->where('order_id', $input['order_id'])->first()) {
            if ($order->receive_user_id != $input['user_id']) {
                return array('status' => 0, 'errmsg' => '接单人不匹配');
            }
            DB::beginTransaction();
            $res1 = DB::table('app_jiaozhuang_order')->where('order_id', '=', $input['order_id'])->update(['is_visit' => 1]);
            $uuid = Uuid::generate(1);
            $array = array(
                'order_id' => $input['order_id'],
                'schedule_name' => '第一次上门',
                'create_time' => Carbon::now(),
                'place' => $input['place'] ? : '未找到位置',
                'user_id' => $input['user_id'],
                'schedule_id' => $uuid->string,
                'status' => 2,
                'repaire_id' => $input['repaire_id']
            );
            $res2 = DB::table('app_jiaozhuang_schedule')->insert($array);
            if ($res1 === 0 || $res2 == false) {
                DB::rollBack();
                return array('status' => 0, 'errmsg' => '上门失败');
            } else {
                DB::commit();
                return array('status' => 1);
            }
        }else{
            return array('status'=>0,'errmsg'=>'参数错误');
        }
    }
    /*添加点位信息*/
    /*
    @param array(
        'repaire_place_name' =>点位信息
        'user_id'=>用户id
        'order_id'=>工单id
        )
    */
    public function addPoint(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'user_id' => 'required',
            'order_id' => 'required',
            'repaire_place_name' => 'required',
        ]);
        if ($validator->fails()) {
            return array('status' => 0, 'errmsg' => '缺失参数!');
        }

        if ($order = DB::table('app_jiaozhuang_order')->where('order_id', '=', $input['order_id'])->first()) {
            if ($order->receive_user_id != $input['user_id']) {
                return array('status' => 0, 'errmsg' => '接单人不匹配');
            }

            $input['repaire_id'] = DB::table('app_jiaozhuang_repaire_user')->where('user_id', '=', $input['user_id'])->value('repaire_id');
            $uuid = Uuid::generate(1);
            $input['repaire_place_id'] = $uuid->string;
            unset($input['user_id']);
            DB::beginTransaction();
            $res = DB::table('app_jiaozhuang_repaire_place')->insert($input);
            DB::table('app_jiaozhuang_order')
                    ->where('order_id', '=', $input['order_id'])
                    ->update(['is_point' => 1]);
            if (!$res) {
                DB::rollback();//事务回滚
                return array('status' => 0, 'errmsg' => '添加点位失败!');
            }else{
                DB::commit();
                return array('status' => 1 , 'data'=>array('repaire_place_id'=>$input['repaire_place_id']));
            }

        }
    }

    public function deletePoint(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'user_id' => 'required',
            'order_id' => 'required',
            'repaire_place_id' => 'required',
        ]);
        if ($validator->fails()) {
            return array('status' => 0, 'errmsg' => '缺失参数!');
        }
        if ($order = DB::table('app_jiaozhuang_order')->where('order_id', '=', $input['order_id'])->first()) {
            if ($order->receive_user_id != $input['user_id']) {
                return array('status' => 0, 'errmsg' => '接单人不匹配');
            }
            DB::beginTransaction();
            $res = DB::table('app_jiaozhuang_repaire_place')->where('repaire_place_id',$input['repaire_place_id'])->delete();
            if(DB::table('app_jiaozhuang_repaire_place')->where('order_id',$input['order_id'])->count()<1){
                DB::table('app_jiaozhuang_order')
                    ->where('order_id', '=', $input['order_id'])
                    ->update(['is_point' => 0]);
            }
            if (!$res) {
                DB::rollback();//事务回滚
                return array('status' => 0, 'errmsg' => '删除点位失败!');
            }else{
                DB::commit();
                return array('status' => 1);
            }
        }
    }

    /*添加进度
    @param array(
        'order_id'
        'user_id'
        'schedule_name'=>进度描述
        'display_order'=>排序
        'place'=>位置
        )
    */
    public function addSchedule(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'user_id' => 'required',
            'order_id' => 'required',
            'schedule_name' => 'required',
            'repaire_id' => 'required',
            'place' => 'required'
        ]);
        if ($validator->fails()) {
            return array('status' => 0, 'errmsg' => '缺失参数!');
        }

        if (DB::table('app_jiaozhuang_order')->where('order_id', '=', $input['order_id'])->value('receive_user_id') != $input['user_id']) {
            return array('status' => 0, 'errmsg' => '工单号与用户不匹配,添加进度失败');
        }

        $uuid = Uuid::generate(1);
        $input['schedule_id'] = $uuid->string;
        $input['status'] = 2;
        $input['place'] = $input['place'] ? : '未找到位置';
        $input['create_time'] = Carbon::now();
        if (DB::table('app_jiaozhuang_schedule')->insert($input)) {
            return array('status' => 1);
        }
    }

    public function getOrder($order_id)
    {
        $data = DB::table('app_jiaozhuang_order')
            ->where('order_id','=',$order_id)
            ->first();
        $data->images = DB::table('app_jiaozhuang_img')->where('order_id',$order_id)->get();
        $data->schedules = DB::table('app_jiaozhuang_schedule')->where('order_id',$order_id)->get();
        $data->places = DB::table('app_jiaozhuang_repaire_place')->where('order_id',$order_id)->get();

        return response()->json($data);
    }


    /*确认完成维修单*/
    public function confirmComplete(Request $request){
    	$input = $request->all();
    	$validator = Validator::make($input, [
    	    'user_id' => 'required',
    	  	'order_id' => 'required',
            'repaire_id' => 'required'
    	]);
    	if ($validator->fails()) {
    	    return array('status' => 0, 'errmsg' => '缺失参数!');
    	}
    	if (DB::table('app_jiaozhuang_order')->where('order_id',$input['order_id'])->value('receive_user_id') != $input['user_id']) {
            return array('status' => 0, 'errmsg' => '工单号与用户不匹配,添加进度失败');
        }
        $money = DB::table('app_jiaozhuang_repaire_project')->where('repaire_id',$input['repaire_id'])->value('money');
        $num = DB::table('app_jiaozhuang_repaire_place')->where('order_id',$input['order_id'])->count();
        DB::beginTransaction();
    	$res = DB::table('app_jiaozhuang_order')
        ->where('order_id',$input['order_id'])
        ->update(['state'=>3,'create_time'=>Carbon::now(),'total_money'=>$money*$num]);

        $uuid = Uuid::generate(1);
        $insert['schedule_id'] = $uuid->string;
        $insert['status'] = 2;
        $insert['place'] = $input['place'] ? : '未找到位置';
        $insert['create_time'] = Carbon::now();
        $insert['schedule_name'] = '待评价';
        $res2 = DB::table('app_jiaozhuang_schedule')->insert($insert);

    	if($res && $res2){
            DB::commit();
    		return array('status'=>1);
    	}else{
            DB::rollback();
    		return array('status'=>0,'errmsg'=>'确认失败!');
    	}
    }
    /*创建工单*/
    /**
     * @param Request $request
     * @return array
     */
    public function createOrder(Request $request){
        $count = DB::table('app_jiaozhuang_order')->where('create_time','>=', Carbon::today())->sharedLock()->count();
        $orderNo = date('YmdHis',time()).($count+1);
        $input = $request->all();
        $validator = Validator::make($input, [
            'user_id' => 'required',
            'order_desc' => 'required',
            'place' => 'required',
            'channel_id' => 'required',
            'mobile' => 'required'
        ]);
        if ($validator->fails()) {
            return array('status' => 0, 'errmsg' => '缺失参数!');
        }
        $now = Carbon::now();
        $uuid = Uuid::generate(1);
        $data = array(
            'order_id'=> $uuid->string,
            'order_no'=> $orderNo,
            'order_desc'=>$input['order_desc'],
            'state'=>1,
            'repaire_time'=>$now,
            'create_time'=>$now,
            'creator_id'=>$input['user_id'],
            'user_name'=>Predis::hget('user:'.$input['user_id'],'displayName'),
            'mobile'=>$input['mobile'],
            'org_name'=>Predis::hget('channel:'.$input['channel_id'],'channelName'),
            'channel_id'=>$input['channel_id'],
            'place'=>$input['place'],
            'order_flag'=>1,
            'latitude' => $input['latitude'],
            'receive_status'=>0
        );
        $img = array();
        foreach ($input['img'] as $key => $item) {
            $_uuid = Uuid::generate(1);
            $img[$key] = $item;
            $img[$key]['img_id'] = $_uuid->string;
            $img[$key]['order_id'] = $data['order_id'];
            $img[$key]['create_time'] = $now;
            $img[$key]['user_id'] = $input['user_id'];
        }
        DB::beginTransaction();
        $res1 = DB::table('app_jiaozhuang_img')->insert($img);
        $res2 = DB::table('app_jiaozhuang_order')->insert($data);
        if($res1 && $res2){
            DB::commit();
            return array('status'=>1);
        }else{
            DB::rollBack();
            return array('status'=>0,'errmsg'=>'创建工单失败，请重试');
        }
    }

    /*报修人员工单列表*/
    public function orderList($user_id){
        $res = DB::table('app_jiaozhuang_order')->where('creator_id',$user_id)->select('order_id','state','order_desc','repaire_time')->get();
        if(empty($res)){
            return array('status'=>0,'errmsg'=>'还没有工单信息');
        }
        foreach ($res as $key => $value) {
            $data[$key] = $value;
            $data[$key]->schedules = DB::table('app_jiaozhuang_schedule')->where('order_id',$value->order_id)->orderBy('create_time','desc')->select('create_time','schedule_name')->take(1)->get();
        }
        return array('status'=>1,'data'=>$data);
    }

    public function evaluate(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'user_id' => 'required',
            'order_id' => 'required',
            'eval_level' => 'required',
            'eval_content' => 'required'
        ]);
        if ($validator->fails()) {
            return array('status' => 0, 'errmsg' => '缺失参数!');
        }
        if($data = DB::table('app_jiaozhuang_order')->where('order_id',$input['order_id'])->where('creator_id',$input['user_id'])->first()){
            $now = Carbon::now();
            if(DB::table('app_jiaozhuang_order')->where('order_id',$input['order_id'])->where('creator_id',$input['user_id'])->update([
                'eval_level'=>$input['eval_level'],
                'eval_content'=>$input['eval_content'],
                'state'=>4,
                'create_time'=>$now,
                'finish_time'=>$now
                ])){

                $uuid = Uuid::generate(1);
                $insert['schedule_id'] = $uuid->string;
                $insert['status'] = 2;
                $insert['place'] = $input['place'] ? : '未找到位置';
                $insert['create_time'] = Carbon::now();
                $insert['schedule_name'] = '已完成';
                DB::table('app_jiaozhuang_schedule')->insert($insert);

                return array('status'=>1);
            }else{
                return array('status'=>0,'errmsg'=>'评价失败,请重试');
            }
        }else{
            return array('status'=>0,'errmsg'=>'参数不正确');
        }
    }










}
