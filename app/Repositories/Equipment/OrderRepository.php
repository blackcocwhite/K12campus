<?php
/**
 * Created by PhpStorm.
 * User: Jh
 * Date: 2017/3/23
 * Time: 17:19
 */

namespace App\Repositories\Equipment;

use App\Model\Equipment\Order;
use App\Model\Equipment\ChannelEquipment;

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
        $data = ['order_id','state','create_time','order_desc','creator_id','place','user_name','mobile','org_name','channel_id','repaire_time','is_point','is_visit'];
        return Order::where('order_id', $order_id)
            ->select($data)
            ->with('images','schedules','places')
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
            ->with(['schedules' => function ($query) {
                $query->select('schedule_id','order_id','schedule_name','create_time')
                    ->orderBy('create_time','desc')
                    ->take(1);
            }])
            ->select($data)
            ->orderBy('repaire_time','desc')
            ->paginate($this->page);
    }

    /**
     * 待处理订单
     * @param $repair_id
     * @return mixed
     */
    public function pendingOrder($repair_id){
        $data = ['order_id','order_desc','state','repaire_time','channel_equipment_id'];
        $res = ChannelEquipment::where('repair_id',$repair_id)->with(['orders'=>function($query) use($data) {
            $query->where('state', 2)->where('receive_status', 0)->select($data)->with(['schedules' => function ($query) {
                $query->select('schedule_id', 'order_id', 'schedule_name', 'create_time')
                    ->orderBy('create_time', 'desc')
                    ->take(1);
            }])->first()->toArray();
        }]);
        return $res['orders'];
    }

    public function handingOrder($repair_id)
    {
        $data = ['order_id', 'order_desc', 'state', 'repaire_time', 'channel_equipment_id'];
        $res = ChannelEquipment::where('repair_id', $repair_id)->with(['orders' => function ($query) use ($data) {
            $query->where('state', 2)->where('receive_status', 1)->where('receive_user_id', $_SERVER['HTTP_AUTHORIZATION'])->select($data)->with(['schedules' => function ($query) {
                $query->select('schedule_id', 'order_id', 'schedule_name', 'create_time')
                    ->orderBy('create_time', 'desc')
                    ->take(1);
            }])->first()->toArray();
        }]);
        return $res['orders'];
    }


    protected function forUser(){

    }
}