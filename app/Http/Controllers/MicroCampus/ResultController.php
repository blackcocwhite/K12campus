<?php
/**
 * Created by PhpStorm.
 * User: Jh
 * Date: 2017/6/13
 * Time: 16:37
 */

namespace App\Http\Controllers\MicroCampus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use DB;
use Validator;

class ResultController extends Controller
{
    protected $user;

    public function __construct(UserRepository $user)
    {
        $this->user = $user;
    }

    public function index($channel_id)
    {
        $group = $this->user->getGroup($_SERVER['HTTP_AUTHORIZATION'],$channel_id);
        if(empty($group)){
            return array('status'=>0,'errmsg'=>'not found student');
        }
        $student_id = $this->getStudentId($group);
        $result = DB::table('base_school_subject_score')
            ->leftJoin('base_school_exam','base_school_exam.exam_id','=','base_school_subject_score.exam_id')
            ->join('console_student','base_school_subject_score.student_id','=','console_student.student_id')
            ->select('base_school_subject_score.exam_id','exam_name','base_school_exam.subject_id','base_school_exam.create_time','base_school_subject_score.student_id','student_name AS studentName')
            ->whereIn('base_school_subject_score.student_id',$student_id)
            ->orderBy('create_time','desc')
            ->paginate()
            ->all();
        if(count($result) < 1){
            return array('status'=>0,'errmsg'=>'not found data');
        }
        return array('status'=>1,'data'=>$result);
    }

    private function getStudentId($group)
    {
        $data = array_filter($group,function($item){
            return ($item['user_type'] == 1);
        });
        return array_map(function ($item){
            return $item['student_id'];
        },$data);
    }

    public function show($exam_id,$student_id){
        $result = DB::table('base_school_subject_score')
            ->join('console_subject','base_school_subject_score.subject_id','=','console_subject.subject_id')
            ->join('console_student','base_school_subject_score.student_id','=','console_student.student_id')
            ->where('exam_id',$exam_id)
//            ->where('base_school_subject_score.student_id',$student_id)
            ->select('subject_name','console_subject.subject_id','score','create_time','student_name AS studentName','base_school_subject_score.student_id')
            ->get()
            ->all();
        if(count($result) < 1){
            return array('status'=>0,'errmsg'=>'not found data');
        }
        return array('status'=>1,'data'=>$result);
    }

    public function statistics(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'student_id' => 'required',
            'subject_id' => 'required',
        ]);
        if($validator->fails()){
            return response()->json(['status'=>0,'errmsg'=>'参数不正确！'],403);
        }
        $result = DB::table('base_school_subject_score')
            ->join('base_school_exam','base_school_subject_score.exam_id','=','base_school_exam.exam_id')
            ->where('student_id',$request->input('student_id'))
            ->where('base_school_subject_score.subject_id',$request->input('subject_id'))
            ->select('score', 'base_school_exam.exam_name')
            ->get()
            ->all();
        if( count( $result ) < 1 ){
            return response()->json(['status'=>0,'errmsg'=>'data not found'],404);
        }

        return response()->json(['status' => 1, 'data' => $result]);
    }
}
