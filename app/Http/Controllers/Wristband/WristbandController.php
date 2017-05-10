<?php

namespace App\Http\Controllers\Wristband;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Predis;
use DB;
use App\Model\Wristband\Student;
use Uuid;
use Validator;

class WristbandController extends Controller
{
    /**
     * 家长端到校通知列表
     * @param Request $request
     * @return array
     */
    public function notifyList(Request $request)
    {
        $input = $request->all();
        $_c = Predis::smembers("userType:$input[channel_id]:$_SERVER[HTTP_AUTHORIZATION]");
        $student_id = [];
        foreach ($_c as $item) {
            $list = explode(':', $item);
            if ($list[1] == 1) {
                $student_id[] = Predis::hget("group.member:$list[0]:$_SERVER[HTTP_AUTHORIZATION]", "studentId");
            }
        }

        $da_id = Student::whereIn('student_id', $student_id)->lists('da_id');
        $result = DB::table('app_shouhuan_data')->
        leftJoin('app_shouhuan_da_student', 'app_shouhuan_data.da_id', '=', 'app_shouhuan_da_student.da_id')
            ->whereIn('app_shouhuan_data.da_id', $da_id)
            ->select('app_shouhuan_data.flag', 'create_time', 'student_id', 'app_shouhuan_data.da_id')
            ->whereBetween('create_time', [Carbon::today()->startOfWeek()->format('Y-m-d'), Carbon::today()->endOfWeek()->format('Y-m-d')])
            ->orderBy('create_time', 'desc')
            ->get();
        $v = [];
        foreach ($result as $key => $item) {
            $v[$key] = objectToArray($item);
            $v[$key]['studentName'] = Predis::hget("student:$input[channel_id]:" . $v[$key]['student_id'], 'studentName');
        }
        return $v;
    }

    /**
     * 家长端请假列表
     * @param Request $request
     * @return array
     */
    public function leaveListForParent(Request $request)
    {
        $input = $request->all();
        $_c = Predis::smembers("userType:$input[channel_id]:$_SERVER[HTTP_AUTHORIZATION]");
        $student_id = [];
        foreach ($_c as $item) {
            $list = explode(':', $item);
            if ($list[1] == 1) {
                $student_id[] = Predis::hget("group.member:$list[0]:$_SERVER[HTTP_AUTHORIZATION]", "studentId");
            }
        }

        $result = DB::table('app_shouhuan_leave')
            ->whereIn('student_id', $student_id)
            ->select('student_id', 'start_time', 'end_time', 'check_status')
            ->orderBy('create_time', 'desc')
            ->get();

        $v = [];
        foreach ($result as $key => $item) {
            $v[$key] = objectToArray($item);
            $v[$key]['studentName'] = Predis::hget("student:$input[channel_id]:" . $v[$key]['student_id'], 'studentName');
        }
        return $v;
    }

    /**
     * 请假申请
     * @param Request $request
     * @return array
     */
    public function askLeave(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'student_id' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'channel_id' => 'required',
            'leave_desc' => 'required',
            'group_id' => 'required'
        ]);
        if ($validator->fails()) {
            return array('status' => 0, 'errmsg' => '缺失参数!');
        }
        //$time = Carbon::now()->subHours(12);

        //$res = DB::table('app_shouhuan_leave')
        //  ->where('student_id',$input['student_id'])
        //    ->where('create_time','>',$time)->get();
        $string = Uuid::generate(1);
        $input['leave_id'] = $string->string;
        $input['user_id'] = $_SERVER['HTTP_AUTHORIZATION'];
        $input['check_status'] = 1;
        $input['create_time'] = Carbon::now();
        if (DB::table('app_shouhuan_leave')->insert($input)) {
            return array('status' => 1);
        } else {
            return array('status' => 0, 'errmsg' => '请假申请失败');
        }

    }

    /**
     * 家长关联的学生列表
     * @param $channel_id
     * @return array
     */
    public function hasStudent($channel_id)
    {
        $_c = Predis::smembers("userType:$channel_id:$_SERVER[HTTP_AUTHORIZATION]");
        if (empty($_c)) {
            return array('status' => 0, 'errmsg' => '没有数据');
        }
        $student_id = [];
        foreach ($_c as $key => $item) {
            $list = explode(':', $item);
            if ($list[1] == 1) {
                $student_id[$key]['group_id'] = $list[0];
                $student_id[$key]['student_id'] = Predis::hget("group.member:$list[0]:$_SERVER[HTTP_AUTHORIZATION]", "studentId");
                $student_id[$key]['studentName'] = Predis::hget("student:$channel_id:" . $student_id[$key]['student_id'], 'studentName');
            }
            sort($student_id);
        }
        return array('status' => 1, 'data' => $student_id);
    }

    /**
     * 老师端请假列表
     * @param Request $request
     * @return array
     */
    public function leaveListForTeacher(Request $request)
    {
        $input = $request->all();
        $_c = Predis::smembers("userType:$input[channel_id]:$_SERVER[HTTP_AUTHORIZATION]");
        if (empty($_c)) {
            return array('status' => 0, 'errmsg' => '没有数据');
        }
        $group_list = [];
        foreach ($_c as $key => $item) {
            $list = explode(':', $item);
            if ($list[1] == 2 || $list[1] == 3) {
                $group_list[$key] = $list[0];
            }
            sort($group_list);
        }
        if (empty($group_list)) return array('status' => 0, 'errmsg' => '您没有权限');
        DB::setFetchMode(\PDO::FETCH_ASSOC);
        $res = DB::table('app_shouhuan_leave')->whereIn('group_id', $group_list)->select('leave_id', 'student_id', 'start_time', 'end_time', 'check_status', 'group_id')->get();
        $result = [];
        foreach ($res as $key => $value) {
            $result[$key] = $value;
            $result[$key]['studentName'] = Predis::hget("student:$input[channel_id]:" . $value['student_id'], 'studentName');
            $result[$key]['groupName'] = Predis::hget("group:$value[group_id]", "groupName");
        }

        return $result;
    }

    /**
     * 请假单详细信息
     * @param $leave_id
     * @return array|mixed|static
     */
    public function leaveInfo($leave_id)
    {
        DB::setFetchMode(\PDO::FETCH_ASSOC);
        $res = DB::table('app_shouhuan_leave')->where('leave_id', $leave_id)->first();
        if (!$res) return array('status' => 0, 'errmsg' => '没有数据！');
        $res['studentName'] = Predis::hget("group.member:$res[group_id]:$res[user_id]", 'studentName');
        $res['groupName'] = Predis::hget("group:$res[group_id]", "groupName");
        $res['relation'] = Predis::hget("parent.student:$res[user_id]:$res[student_id]", "relation");
        return $res;
    }

    /**
     * 审核请假申请
     * @param Request $request
     * @return array
     */
    public function admitLeave(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'leave_id' => 'required',
            'channel_id' => 'required',
            'admit' => 'required|numeric|max:2|min:0'
        ]);
        if ($validator->fails()) {
            return array('status' => 0, 'errmsg' => '缺失参数!');
        }
        $_c = Predis::smembers("userType:$input[channel_id]:$_SERVER[HTTP_AUTHORIZATION]");
        if (empty($_c)) {
            return array('status' => 0, 'errmsg' => '没有数据');
        }
        $group_list = [];
        foreach ($_c as $key => $item) {
            $list = explode(':', $item);
            if ($list[1] == 2 || $list[1] == 3) {
                $group_list[$key] = $list[0];
            }
            sort($group_list);
        }
        if (empty($group_list)) return array('status' => 0, 'errmsg' => '您没有权限');

        if (DB::table('app_shouhuan_leave')->where('leave_id', $input['leave_id'])->update(['check_status' => $input['admit']])) {
            return array('status' => 1);
        } else {
            return array('status' => 0, 'errmsg' => '同意审核失败');
        }

    }

    public function attendance(Request $request)
    {
        $channel_id = $request->input('channel_id');
        $_c = Predis::smembers("userType:$channel_id:$_SERVER[HTTP_AUTHORIZATION]");
        if (empty($_c)) {
            return array('status' => 0, 'errmsg' => '没有数据');
        }
        $group_list = [];
        foreach ($_c as $key => $item) {
            $list = explode(':', $item);
            if ($list[1] == 2 || $list[1] == 3) {
                $group_list[$key]['groupId'] = $list[0];
                $group_list[$key]['groupName'] = Predis::hget("group:$list[0]", "groupName");
            }
            sort($group_list);
        }

        if ($request->has('group_id')) {
            $group_id = $request->input('group_id');
        } else {
            $group_id = $group_list[0];
        }

        $start_time = $request->has('end_time') ? $request->input('start_time') : Carbon::today();
        $end_time = $request->has('end_time') ? $request->input('end_time') : Carbon::tomorrow();

        $total_student = Predis::hlen("group.student:$group_id");
        $student_list = Predis::hvals("group.student:$group_id");
        $da_id = Student::whereIn('student_id', $student_list)->lists('da_id');
        $late = DB::table('app_shouhuan_data')
            ->whereIn('da_id', $da_id)
            ->where('flag', 0)
            ->whereBetween('create_time', [$start_time, $end_time])
            ->count();
        $early = DB::table('app_shouhuan_data')
            ->whereIn('da_id', $da_id)
            ->where('flag', 3)
            ->whereBetween('create_time', [$start_time, $end_time])
            ->count();
        $data = array(
            'normal' => $total_student - $late,
            'late' => $late,
            'early' => $early,
            'groupName' => Predis::hget("group:$group_id", "groupName"),
            'groupList' => $group_list
        );
        return $data;
    }
}
