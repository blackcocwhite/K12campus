<?php
namespace App\Repositories\Wristband;

use Redis;
use DB;

class InformRepository
{
    public function __construct()
    {

    }

    public function getStudentId(string $channel_id)
    {
        $list = Redis::sMembers("userType:$channel_id:$_SERVER[HTTP_AUTHORIZATION]");
        $data = [];
        foreach ($list as $key => $item) {
            $_cache = explode(':', $item);
            if ($_cache[1] == 1) {
                $data['group_id'][$key] = $_cache[0];
                $data['student_id'][$key] = Redis::hget("group.member:$_cache[0]:$_SERVER[HTTP_AUTHORIZATION]", "studentId");
                $data['student_name'][$key] = Redis::hget("student:$channel_id:" . $data['student_id'][$key], 'studentName');
            }
        }
        if (empty($data)) return array('status' => 0, 'errmsg' => "no data");

        return array('status' => 1, 'data' => $data);
    }

    public function listForNotify($channel_id, $start_time, $end_time)
    {
        $cache = $this->getStudentId($channel_id);
        if( $cache['status'] === 0 ){
            return $cache;
        }
        $result = DB::table('app_shouhuan_data')
            ->Join('app_shouhuan_da_student', 'app_shouhuan_data.da_id', '=', 'app_shouhuan_da_student.da_id')
            ->Join('console_student', 'app_shouhuan_da_student.student_id', '=', 'console_student.student_id')
            ->whereIn('app_shouhuan_da_student.student_id', $cache['data']['student_id'])
            ->select('app_shouhuan_data.flag', 'create_time', 'app_shouhuan_da_student.student_id', 'console_student.student_name as studentName')
            ->whereBetween('create_time', [$start_time, $end_time])
            ->orderBy('create_time', 'desc')
            ->get();

        if($result->isEmpty()){
            return array('status' => 0, 'errmsg' => 'no data');
        }else{
            return $result;
        }

    }
}
