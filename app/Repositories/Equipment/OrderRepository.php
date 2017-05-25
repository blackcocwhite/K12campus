<?php
/**
 * Created by PhpStorm.
 * User: Jh
 * Date: 2017/3/23
 * Time: 17:19
 */

namespace App\Repositories\Equipment;

use App\Model\Equipment\Order;
use App\Model\Equipment\OrderSchedule;
use Uuid;

class OrderRepository
{
    protected $page;
    public function __construct()
    {
        $this->page = config('equipment.page');
    }
    /**
     * 获取指定指定工单号的工单详情
     * @param  order_id
     * @return Collection
     */
    public function forOrderId($order_id)
    {
        $data = ['order_id', 'state', 'create_time', 'order_desc', 'creator_id', 'place', 'user_name', 'mobile', 'org_name', 'channel_id', 'repaire_time', 'is_point', 'is_visit', 'receive_status', 'eval_level', 'eval_content'];
        return Order::where('order_id', $order_id)
            ->select($data)
            ->with('images', 'places')
            ->with(['Schedules' => function ($query) {
                $query->select('schedule_id', 'order_id', 'schedule_name', 'create_time','place')
                    ->orderBy("create_time","desc")
                    ->get();
            }])
            ->get();
    }
    /**
     * 报修商的报修工单列表
     * @param $user_id
     * @param $status
     * @param $page
     * @return mixed
     */
    public function forTeacher($user_id)
    {
        $data = ['order_id','order_desc','state','repaire_time'];
        return Order::where('creator_id', $user_id)
            ->with(['latestSchedules' => function ($query) {
                $query->select('schedule_id','order_id','schedule_name','create_time')
                    ->get();
            }])
            ->select($data)
            ->orderBy('repaire_time','desc')
            ->paginate($this->page);
    }

    public function pendingOrder($repair_id){
        $data = ['order_id','order_desc','state','repaire_time','channel_equipment_id'];
        $res = Order::where('repaire_id',$repair_id)
            ->where('state',2)
            ->where('receive_status',0)
            ->select($data)
            ->with(['latestSchedules' => function ($query) {
                $query->select('schedule_id','order_id','schedule_name','create_time')
                    ->get();
            }])
            ->paginate($this->page);

        return $res;
    }

    public function handingOrder($repair_id){
        $data = ['order_id','order_desc','state','repaire_time','channel_equipment_id'];
        $res = Order::where('repaire_id',$repair_id)
            ->where('state',2)
            ->where('receive_status',1)
            ->where('receive_user_id',$_SERVER['HTTP_AUTHORIZATION'])
            ->select($data)
            ->with(['latestSchedules' => function ($query) {
                $query->select('schedule_id','order_id','schedule_name','create_time')
                    ->get();
            }])
            ->paginate($this->page);
        return $res;
    }

    public function completeOrder($repair_id){
        $data = ['order_id','order_desc','state','repaire_time','channel_equipment_id'];
        $res = Order::where('repaire_id',$repair_id)
            ->where('state',3)
            ->where('receive_status',1)
            ->where('receive_user_id',$_SERVER['HTTP_AUTHORIZATION'])
            ->select($data)
            ->with(['latestSchedules' => function ($query) {
                $query->select('schedule_id','order_id','schedule_name','create_time')
                    ->get();
            }])
            ->paginate($this->page);
        return $res;
    }

    public function evaluatedOrder($repair_id){
        $data = ['order_id','order_desc','state','repaire_time','channel_equipment_id'];
        $res = Order::where('repaire_id',$repair_id)
            ->where('state',4)
            ->where('receive_status',1)
            ->where('receive_user_id',$_SERVER['HTTP_AUTHORIZATION'])
            ->select($data)
            ->with(['latestSchedules' => function ($query) {
                $query->select('schedule_id','order_id','schedule_name','create_time')
                    ->get();
            }])
            ->paginate($this->page);

        return $res;
    }

    public function addSchedules($desc,$place,$user_id,$repaire_id,$order_id,$time,$status=-1){
        $uuid = Uuid::generate(1);
        $_data = array(
            'order_id' => $order_id,
            'schedule_name' => $desc,
            'create_time' => $time,
            'place' => $place,
            'user_id' => $user_id,
            'schedule_id' => $uuid->string,
            'status' => $status,
            'repaire_id' => $repaire_id
        );

        return OrderSchedule::create($_data);
    }

    public function allOrders($repair_id){
        $data = ['order_id','order_desc','state','repaire_time','channel_equipment_id'];
        $res = Order::where('repaire_id',$repair_id)
            ->with(['latestSchedules' => function ($query) {
                $query->select('schedule_id','order_id','schedule_name','create_time')
                    ->get();
            }])
            ->select($data)
            ->paginate($this->page);

        return $res;
    }
}