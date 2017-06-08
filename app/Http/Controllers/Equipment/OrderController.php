<?php
/**
 * Created by PhpStorm.
 * User: Jh
 * Date: 2017/3/24
 * Time: 10:28
 */

namespace App\Http\Controllers\Equipment;

use Illuminate\Http\Request;
use Uuid;
use Validator;
use App\Repositories\Equipment\OrderRepository;
use App\Model\Equipment\OrderImg;
use App\Model\Equipment\Order;
use App\Model\Equipment\User;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use DB;
use Predis;

class OrderController extends Controller
{
    protected $order;
    public function __construct(OrderRepository $order)
    {
        $this->order = $order;
    }

    /**
     * 报修端的报修列表
     * @param Request $request
     * @return mixed
     */
    public function repairOrderList(){
        return $this->order->forTeacher($_SERVER['HTTP_AUTHORIZATION']);
    }

    /**
     * 工单的详情
     * @param $order_id
     * @return \App\Repositories\Collection
     */
    public function orderInformation($order_id){
        return $this->order->forOrderId($order_id);
    }

    /**
     * 创建工单
     * @param Request $request
     * @return array
     */
    public function create(Request $request){
        $input = $request->all();

        $validator = Validator::make($input, [
            'order_desc' => 'required',
            'place' => 'required',
            'channel_id' => 'required',
            'mobile' => 'required',
            'img' => 'required'
        ]);
        if ($validator->fails()) {
            return array('status' => 0, 'errmsg' => '缺失参数!');
        }

        $count = Order::where('create_time','>=', Carbon::today())->count();
        $orderNo = date('YmdHis',time()).($count+1);
        $now = Carbon::now();
        $uuid = Uuid::generate(1);
        $input += array(
            'order_id'=> $uuid->string,
            'order_no'=> $orderNo,
            'repaire_time'=>$now,
            'create_time'=>$now,
            'creator_id'=>$_SERVER['HTTP_AUTHORIZATION'],
            'user_name'=>Predis::hget('user:'.$_SERVER['HTTP_AUTHORIZATION'],'displayName'),
            'org_name'=>Predis::hget('channel:'.$input['channel_id'],'channelName'),
            'order_flag'=>1,
        );
        $img = array();
        foreach ($input['img'] as $key => $item) {
            $_uuid = Uuid::generate(1);
            $img[$key] = $item;
            $img[$key]['img_id'] = $_uuid->string;
            $img[$key]['order_id'] = $input['order_id'];
            $img[$key]['create_time'] = $now;
            $img[$key]['user_id'] = $_SERVER['HTTP_AUTHORIZATION'];
        }
        $images=[];
        foreach($img as $v){
            $images[] = new OrderImg($v);
        }
        unset($input['img']);
        $save = Order::create($input);
        $save->images()->saveMany($images);
        if($save){
            return array('status'=>1);
        }else{
            return array('status'=>0,'创建工单失败！');
        }
    }

    /**
     * 待处理工单列表
     * @param $repaire_id
     * @return mixed
     */
    public function pendingOrderList(){
        $repaire_id = User::where('user_id',$_SERVER['HTTP_AUTHORIZATION'])->value('repaire_id');
        return $this->order->pendingOrder($repaire_id);
    }

    /*
     * 处理中工单列表
     */
    public function handingOrderList(){
        $repaire_id = User::where('user_id',$_SERVER['HTTP_AUTHORIZATION'])->value('repaire_id');
        return $this->order->handingOrder($repaire_id);
    }

    /*
     * 已完成工单列表
     */
    public function completeOrderList(){
        $repaire_id = User::where('user_id',$_SERVER['HTTP_AUTHORIZATION'])->value('repaire_id');
        return $this->order->completeOrder($repaire_id);
    }

    /*
     * 已评价工单列表
     */
    public function evaluatedOrderList(){
        $repaire_id = User::where('user_id',$_SERVER['HTTP_AUTHORIZATION'])->value('repaire_id');
        return $this->order->evaluatedOrder($repaire_id);
    }

    /**
     * 接受工单
     * @param $request
     */
    public function receiveOrder(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'order_id' => 'required',
            'place' => 'required'
        ]);

        if ($validator->fails()) {
            return array('status' => 0, 'errmsg' => '缺失参数!');
        }

        $repaire_id = User::where('user_id',$_SERVER['HTTP_AUTHORIZATION'])->value('repaire_id');
        if($repaire_id == Order::where('order_id',$input['order_id'])->value('repaire_id')){
            $now = Carbon::now();
            DB::beginTransaction();
            $res1 = Order::where('order_id',$input['order_id'])->where('receive_status',0)
                ->update([
                    'receive_user_id' => $_SERVER['HTTP_AUTHORIZATION'],
                    'receive_status' => 1,
                    'create_time'=>$now,
                    'receive_time' => $now
                ]);
            $res2 = $this->order->addSchedules('接单成功',$input['place'],$_SERVER['HTTP_AUTHORIZATION'],$repaire_id,$input['order_id'],$now);
            if($res1 && $res2){
                DB::commit();
                return array('status'=>1);
            }else{
                DB::rollBack();
                return array('status'=>0,'errmsg'=>'接受工单失败，请重试');
            }
        }else{
            return array('status'=>0,'errmsg'=>'您没有权限接单');
        }
    }

    //确认上门
    public function confirmVisit(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'order_id' => 'required',
            'repaire_id' => 'required',
            'place' => 'required'
        ]);

        if ($validator->fails()) {
            return array('status' => 0, 'errmsg' => '缺失参数!');
        }

        if($_SERVER['HTTP_AUTHORIZATION'] == Order::where('order_id',$input['order_id'])->value('receive_user_id')){
            $now = Carbon::now();
            DB::beginTransaction();
            $res1 = Order::where('order_id',$input['order_id'])->where('receive_status',1)
                ->update([
                    'is_visit' => 1,
                    'create_time'=>$now,
                ]);
            $res2 = $this->order->addSchedules('第一次上门',$input['place'],$_SERVER['HTTP_AUTHORIZATION'],$input['repaire_id'],$input['order_id'],$now,2);
            if($res1 && $res2){
                DB::commit();
                return array('status'=>1);
            }else{
                DB::rollBack();
                return array('status'=>0,'errmsg'=>'确认上门失败，请重试');
            }
        }

    }

    //添加点位
    public function addPoint(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'order_id' => 'required',
            'repaire_place_name' => 'required',
        ]);
        if ($validator->fails()) {
            return array('status' => 0, 'errmsg' => '缺失参数!');
        }
        if($_SERVER['HTTP_AUTHORIZATION'] == Order::where('order_id',$input['order_id'])->value('receive_user_id')){
            $uuid = Uuid::generate(1);
            $_insert = array(
                'repaire_place_id' => $uuid->string,
                'repaire_place_name' => $input['repaire_place_name'],
                'repaire_id' => User::where('user_id',$_SERVER['HTTP_AUTHORIZATION'])->value('repaire_id'),
                'order_id' => $input['order_id']
            );
            $res = DB::table('app_jiaozhuang_repaire_place')->insert($_insert);
            if($res){
                return array('status'=>1,'data'=>array('repaire_place_id'=>$_insert['repaire_place_id']));
            }else{
                return array('status'=>0,'errmsg'=>'添加点位失败，请重试');
            }
        }
    }

    //删除点位
    public function deletePoint(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'order_id' => 'required',
            'repaire_place_id' => 'required',
        ]);
        if ($validator->fails()) {
            return array('status' => 0, 'errmsg' => '缺失参数!');
        }
        if($_SERVER['HTTP_AUTHORIZATION'] == Order::where('order_id',$input['order_id'])->value('receive_user_id')){

            $res = DB::table('app_jiaozhuang_repaire_place')->where('repaire_place_id',$input['repaire_place_id'])->delete();
            if ($res) {
                return array('status' => 1);
            }else{
                return array('status' => 0, 'errmsg' => '删除点位失败!');
            }
        }
    }

    //添加进度
    public function addSchedule(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'order_id' => 'required',
            'schedule_name' => 'required',
            'repaire_id' => 'required',
            'place' => 'required'
        ]);
        if ($validator->fails()) {
            return array('status' => 0, 'errmsg' => '缺失参数!');
        }
        if(!$_SERVER['HTTP_AUTHORIZATION'] == Order::where('order_id',$input['order_id'])->value('receive_user_id')) {
            return array('status' => 0, 'errmsg' => '工单号与用户不匹配,添加进度失败');
        }
        if ($this->order->addSchedules($input['schedule_name'], $input['place'], $_SERVER['HTTP_AUTHORIZATION'], $input['repaire_id'], $input['order_id'], Carbon::now())) {
            return array('status' => 1);
        }else{
            return array('status' => 0, 'errmsg' => '添加进度失败,请重试');
        }
    }

    //确认完成维修订单
    public function confirmComplete(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'order_id' => 'required',
            'repaire_id' => 'required',
            'place' => 'required'
        ]);
        if ($validator->fails()) {
            return array('status' => 0, 'errmsg' => '缺失参数!');
        }
        if(!$_SERVER['HTTP_AUTHORIZATION'] == Order::where('order_id',$input['order_id'])->value('receive_user_id')) {
            return array('status' => 0, 'errmsg' => '工单号与用户不匹配,添加进度失败');
        }

        $money = DB::table('app_jiaozhuang_repaire_project')->where('repaire_id',$input['repaire_id'])->value('money');
        $num = DB::table('app_jiaozhuang_repaire_place')->where('order_id',$input['order_id'])->count();
//        DB::beginTransaction();
        $res = Order::where('order_id', $input['order_id'])->where('is_visit', 1)
            ->update(['state'=>3,'create_time'=>Carbon::now(),'total_money'=>$money*$num]);
//        $res2 = $this->order->addSchedules('待评价',$input['place'],$_SERVER['HTTP_AUTHORIZATION'],$input['repaire_id'],$input['order_id'],Carbon::now(),2);

        if ($res) {
//            DB::commit();
            return array('status'=>1);
        }else{
//            DB::rollback();
            return array('status'=>0,'errmsg'=>'确认完成失败!');
        }
    }

    //负责人全部工单查看
    public function allOrders(){
        if(!User::where('user_id',$_SERVER['HTTP_AUTHORIZATION'])->value('flag')){
            return array('status'=>0,'errmsg'=>'您没有权限');
        }
        $repaire_id = User::where('user_id',$_SERVER['HTTP_AUTHORIZATION'])->value('repaire_id');
        return $this->order->allOrders($repaire_id);
    }

    /**
     * 评价工单
     * @param Request $request
     * @return array
     */
    public function evaluate(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'order_id' => 'required',
            'eval_level' => 'required',
            'eval_content' => 'required',
            'place' => 'required'
        ]);
        if ($validator->fails()) {
            return array('status' => 0, 'errmsg' => '缺失参数!');
        }
        if($data = Order::where('order_id',$input['order_id'])->where('creator_id',$_SERVER['HTTP_AUTHORIZATION'])->where('state',3)->first()){
            $now = Carbon::now();
            $res = Order::where('order_id', $input['order_id'])->where('creator_id', $_SERVER['HTTP_AUTHORIZATION'])->update([
                'eval_level'=>$input['eval_level'],
                'eval_content'=>$input['eval_content'],
                'state'=>4,
                'create_time'=>$now,
                'finish_time'=>$now
            ]);
            if ($res) {
                return array('status'=>1);
            }else{
                return array('status'=>0,'errmsg'=>'评价失败,请重试');
            }
        }else{
            return array('status'=>0,'errmsg'=>'没有权限');
        }
    }
}
