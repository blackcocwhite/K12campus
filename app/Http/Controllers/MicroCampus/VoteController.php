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
        }

        return response()->json(['status' => 1, 'total' => $total, 'currentPage' => $page, 'time' => time() * 1000, 'data' => $result]);

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
        if ($data['voteRule'] == 2) {
            $time = Carbon::today()->format('Ymd');
            $data['myVote'] = Predis::sCard('a_schoolVote.user:' . $input['openId'] . ':' . $input['voteId'] . "_" . $time);
        }
        $data['items'] = json_decode($data['items'], true);
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
            return response()->json(['status' => 0, 'errmsg' => '参数不正确'], 403);
        }
        $info = Predis::hgetall("a_schoolVote:base:$input[voteId]");
        if ($info['state'] != 1) {
            return response()->json(['status' => 0, 'errmsg' => '投票不存在'], 404);
        }
        $rule = $info['voteRule'];
        $end_time = $info['endTime'];
        $limit = $info['limitTimes'];
        if ($rule == 1) {
            return $this->_saveOneClick($input, $end_time);
        } elseif ($rule == 2) {
            return $this->_saveMoreClick($input, $end_time, $limit);
        }
    }

    public function option(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'openId' => 'required',
            'voteId' => 'required',
            'optionId' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'errmsg' => '参数不正确'], 403);
        }
        $data = Predis::hgetall("a_schoolVote:base:$input[voteId]");

    }

    public function visit($voteId, $openId)
    {
        if (Predis::hget("a_schoolVote:base:$voteId", "state") != 1) {
            return response()->json(['status' => 0, 'errmsg' => '投票不存在'], 404);
        }
        Predis::hIncrby("a_schoolVote:base:$voteId", "visitNum", 1);
        return response()->json(['status' => 1]);
    }

    private function _saveOneClick($array, $end_time)
    {
        $key = 'a_schoolVote.user:' . $array['openId'] . ':' . $array['voteId'];
        if (Predis::exists($key)) {
            return response()->json(['status' => 0, 'errmgs' => '您已经投过票了'], 403);
        }
        $time = Carbon::today()->format('Ymd');
        Predis::hset($key, $array['optionId'], $time);
//        Predis::expireat($key,($end_time)/1000);
        if ($this->save($array)) {
            return response()->json(['status' => 1, 'data' => 'success']);
        } else {
            Predis::hdel($key, $array['optionId']);
            return response()->json(['status' => 0, 'errmgs' => 'fail'], 403);
        }
    }

    private function _saveMoreClick($array, $end_time, $limit)
    {
        $time = Carbon::today()->format('Ymd');
        $key = 'a_schoolVote.user:' . $array['openId'] . ':' . $array['voteId'] . "_" . $time;
        $count = Predis::scard($key);
        if ($count >= $limit || Predis::sIsMember($key, $array['optionId'])) {
            return response()->json(['status' => 0, 'errmgs' => 'Repeat voting'], 403);
        }
        Predis::sadd($key, $array['optionId']);
//        Predis::expireat($key,($end_time)/1000);
        if ($this->save($array)) {
            return response()->json(['status' => 1, 'data' => array('myVote' => $count + 1)]);
        } else {
            Predis::sRem($key, $array['optionId']);
            return response()->json(['status' => 0, 'errmgs' => 'fail'], 403);
        }
    }

    private function save($arr)
    {
        $array = array($arr);
        $string = Uuid::generate(1);
        $array = array_map(function ($item) use ($string) {
            return [
                'vote_id' => $item['voteId'],
                'item_id' => $item['itemId'],
                'option_id' => $item['optionId'],
                'open_id' => $item['openId'],
                'create_time' => Carbon::now(),
                'result_id' => $string->string,
            ];
        }, $array);
        if (DB::table('a_schoolVote_result')->insert($array)) {
            Predis::hincrby("a_schoolVote:base:$arr[voteId]", "voteNum", 1);
            Predis::hincrby("app_schoolVote:result:$arr[voteId]", $arr['optionId'], 1);
            return true;
        } else {
            return false;
        }
    }
}
