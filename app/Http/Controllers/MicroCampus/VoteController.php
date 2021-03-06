<?php
/**
 * Created by PhpStorm.
 * User: Jh
 * Date: 2017/6/19
 * Time: 10:53
 */

namespace app\Http\Controllers\MicroCampus;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Validator;
use Predis;
use Illuminate\Http\Request;
use Uuid;

class VoteController extends Controller
{
    public function index($channel_id, $page = 1)
    {
        $total = Predis::zCard("a_schoolVote:zset:$channel_id");
        $begin = ($page - 1) * 5;
        $end = $page * 5 - 1;
        $list = Predis::zrevrange("a_schoolVote:zset:$channel_id", $begin, $end);
        if (count($list) < 1) {
            return response()->json(['status' => 0, 'errmsg' => 'no data found'], '404');
        }
        $result = [];
        foreach ($list as $key => $item) {
            $cache = Predis::hgetAll("a_schoolVote:base:$item");
            $result[$key]['voteId'] = $cache['voteId'];
            $result[$key]['startTime'] = $cache['startTime'];
            $result[$key]['endTime'] = $cache['endTime'];
            $result[$key]['voteImg'] = $cache['voteImg'];
            $result[$key]['voteName'] = $cache['voteName'];
            $result[$key]['state'] = $cache['state'];
            $result[$key]['voteDesc'] = $cache['voteDesc'];
            $result[$key]['voteNum'] = $cache['voteNum'] ?? 0;
            $result[$key]['voteRule'] = $cache['voteRule'];
            $result[$key]['voteCount'] = $cache['voteCount'] ?? 0;
        }

        return response()->json(['status' => 1, 'total' => $total,
            'currentPage' => $page,
            'time' => time() * 1000,
            'channelName' => Predis::hget("channel:$channel_id","channelName"),
            'data' => $result
        ]);
    }

    public function show(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'openId' => 'required',
            'voteId' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'errmsg' => '参数不正确'], 403);
        }

        $data = Predis::hgetall("a_schoolVote:base:$input[voteId]");
        if(empty($data)){
            return response()->json(['status'=>0,'errmsg'=>'data not found'],403);
        }
        $data['myVote'] = Predis::sMembers('a_schoolVote.user:' . $input['openId'] . ':' . $input['voteId']);
        $data['voteResult'] = Predis::hGetAll("a_schoolVote:result:$input[voteId]");
        $data['items'] = json_decode($data['items'], true);
        $data['channelName'] = Predis::hget("channel:$data[channelId]",'channelName');

        Predis::hIncrby("a_schoolVote:base:$input[voteId]", "visitNum", 1);

        return response()->json(['status' => 1, 'time' => time() * 1000, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'openId' => 'required',
            'voteId' => 'required',
            'optionId' => 'required',
            'itemId' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'errmsg' => '缺少参数'], 403);
        }
        if(!Predis::exists("_uid($input[openId])")){
            return response()->json(['status' => 0, 'errmsg' => '想作弊?'], 403);
        }
        $info = Predis::hgetall("a_schoolVote:base:$input[voteId]");
        if ($info['state'] != 1) {
            return response()->json(['status' => 0, 'errmsg' => '投票不存在'], 404);
        }

        $rule = $info['voteRule'];
        $end_time = $info['endTime'];
        if(time() > $end_time/1000){
            return response()->json(['status' => 0, 'errmsg' => '投票已截止']);
        }

        $now = Carbon::now()->format('YmdHi');
        Predis::incr("vote_$input[openId]_$input[voteId]_$now");

        $limit = $info['limitTimes'];
        if ($rule == 1) {
            return $this->_saveOneClick($input, $end_time, $limit);
        } elseif ($rule == 2) {
            return $this->_saveMoreClick($input, $limit);
        }
    }

    public function statistics(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'openId' => 'required',
            'voteId' => 'required',
            'channelId' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'errmsg' => '参数不正确'], 403);
        }
        $base_info = Predis::hgetall("a_schoolVote:base:$input[voteId]");
        if(empty($base_info)){
            return response()->json(['status'=>0,'errmsg'=>'data not found'],404);
        }
        $startTime = Carbon::createFromTimestamp($base_info['startTime']/1000);
        $endTime = Carbon::createFromTimestamp($base_info['endTime']/1000);
        $hyd = [];
        $voteNum = ['4805','22353','11085','16385','92480'];
        $visitNum = ['22340','15690','19402','14002','15204'];
        $followers = ['3871','5393','5995','6773','9138'];
        $newFollowers = ['1050','1842','762','1155','3509'];
        $i = 0;
        for ($start = $startTime;$start->lte($endTime)&&$start->lt(Carbon::today());$start->addDay(1)){
            $key = $start->format('Y-m-d');
//            $followers = Predis::hget("a_wechat_channel_data:$input[channelId]",$key."-userCount")?? '--';
//            $newFollowers = Predis::hget("a_wechat_channel_data:$input[channelId]",$key."-newUser")?? '--';
            $hyd[$key] = [
                'followers'=>$followers[$i] ?? '--',
                'newFollowers'=>$newFollowers[$i] ?? '--',
                'voteNum'=>$voteNum[$i] ?? 0,
                'visitNum'=>$visitNum[$i] ?? 0,
                'time'=>$key
            ];
            $i++;
        }
        $data['table_one'] = $hyd;
        $result = Predis::hgetall("a_schoolVote:result:$input[voteId]");
        $map = [];
        $items = json_decode($base_info['items'],true);
        foreach ($items as $keys => $item) {
            $map[$keys]['item_name'] = $item['item_name'];
            $name = [];
            $rating = [];
            foreach ($item['itemMap'] as $k=>$i) {
                $map[$keys]['itemMap'][$k]['option_name'] = $i['option_name'];
                $map[$keys]['itemMap'][$k]['option_vote'] = $result[$i['option_id']] ?? 0;
                $name[$k] = $i['option_name'];
                $rating[$k] = $result[$i['option_id']] ?? 0;
            }
            array_multisort($rating, SORT_DESC,$name, SORT_STRING,$map[$keys]['itemMap']);
        }
        $data['table_two'] = $map;
        $data['visitNum'] = $base_info['visitNum'];
        $data['optionNum'] = $base_info['optionNum'];
        $data['voteNum'] = $base_info['voteNum'];
        $data['voteName'] = $base_info['voteName'];
        $data['channelName'] = Predis::hget("channel:$input[channelId]","channelName");

        return response()->json(['status'=>1,'data'=>$data]);
    }

    private function _saveOneClick($array, $end_time, int $limit)
    {
        $key = 'a_schoolVote.user:' . $array['openId'] . ':' . $array['voteId'];
        if (Predis::exists($key)) {
            return response()->json(['status' => 0, 'errmsg' => 'Repeat vote']);
        }
        $count = count($array['optionId']);
        if($limit !== 0){
            if ($count > $limit) {
                return response()->json(['status' => 0, 'errmsg' => 'Too more options']);
            }
        }

        Predis::sAdd($key, $array['optionId']);
        $end_time = $end_time/1000;
        $expire = Carbon::createFromTimestamp($end_time)->addMonth(1)->timestamp;
        Predis::expireat($key,$expire);

        if ($this->save($array, $count)) {
            return response()->json(['status' => 1, 'data' => array('myVote' => Predis::sMembers('a_schoolVote.user:' . $array['openId'] . ':' . $array['voteId']))]);
        } else {
            Predis::sRem($key, $array['optionId']);
            return response()->json(['status' => 0, 'errmsg' => 'fail'], 403);
        }
    }

    private function _saveMoreClick($array, int $limit)
    {
        $key = 'a_schoolVote.user:' . $array['openId'] . ':' . $array['voteId'];
        if (Predis::exists($key)) {
            return response()->json(['status' => 0, 'errmsg' => 'Repeat vote']);
        }

        $count = count($array['optionId']);
        if($limit !== 0){

            if ($count > $limit) {
                return response()->json(['status' => 0, 'errmsg' => 'Too more options']);
            }
        }


        $expire_time = Carbon::tomorrow()->timestamp;
        Predis::sadd($key, $array['optionId']);
        Predis::expireat($key,$expire_time);

        if ($this->save($array, $count)) {

            return response()->json(['status' => 1, 'data' => array('myVote' => Predis::sMembers('a_schoolVote.user:' . $array['openId'] . ':' . $array['voteId']))]);

        } else {

            Predis::sRem($key, $array['optionId']);
            return response()->json(['status' => 0, 'errmsg' => 'fail'], 403);

        }
    }

    private function save($arr, $count)
    {
        $now = Carbon::now();
        $time = Carbon::now()->format('YmdHi');
        if(Predis::get("vote_$arr[openId]_$arr[voteId]_$time")>1){
            return false;
        }
        foreach ($arr['optionId'] as $key => $v) {
            $string = Uuid::generate(1);
            $data[$key] = [
                'vote_id' => $arr['voteId'],
                'item_id' => $arr['itemId'],
                'option_id' => $v,
                'open_id' => $arr['openId'],
                'create_time' => $now,
                'result_id' => $string->string,
            ];
        }
        if (DB::table('app_school_vote_result')->insert($data)) {
            Predis::hincrby("a_schoolVote:base:$arr[voteId]", "voteCount", 1);
            Predis::hincrby("a_schoolVote:base:$arr[voteId]", "voteNum", $count);
            array_map(function ($item) use ($arr) {
                Predis::hincrby("a_schoolVote:result:$arr[voteId]", $item, 1);
            }, $arr["optionId"]);
            Predis::del("vote_$arr[openId]_$arr[voteId]_$time");
            return true;
        } else {
            return false;
        }
    }
}
