<?php
/**
 * Created by PhpStorm.
 * User: Jh
 * Date: 2017/3/24
 * Time: 10:28
 */

namespace App\Http\Controllers\Equipment;

//use App\Http\Requests\Equipment\OrderCreateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Uuid;
use Validator;
use Predis;
use App\Repositories\OrderRepository;
use App\Model\Equipment\OrderImg;
use App\Model\Equipment\Order;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

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
        $input[] = array(
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
            $img[$key]['order_id'] = $input[0]['order_id'];
            $img[$key]['create_time'] = $now;
            $img[$key]['user_id'] = $input['user_id'];
        }
        $images=[];
        foreach($img as $v){
            $images[] = new OrderImg($v);
        }
        $save = Order::create($input[0]);
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
    public function pendingOrderList($repaire_id){
        return $this->order->pendingOrder($repaire_id);
    }

    public function handingOrderList($repaire_id){
        return $this->order->handingOrder();
    }
}