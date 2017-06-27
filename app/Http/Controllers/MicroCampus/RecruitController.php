<?php
namespace App\Http\Controllers\MicroCampus;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;
use Validator;
use Predis;
use Uuid;

class RecruitController extends Controller
{
    public function index($channel_id)
    {
        $data['enrollDesc'] = Predis::hGet("b_school_enroll:$channel_id","enrollDesc");
        $data['enrollContact'] = Predis::hGet("b_school_enroll:$channel_id","enrollContact");

        return response()->json(['status'=>1,'data'=>$data]);
    }

    public function getNation()
    {
        $result = DB::table("console_nation")->orderBy("display_order","asc")->get();
        if(count($result)<1){
            return response()->json(['status'=>0,'errmsg'=>'data not found'],404);
        }
        return response()->json(['status'=>1,'data'=>$result]);
    }

    public function getRegion($parent_id)
    {
        $result = DB::table('console_region')->where('parent_id',$parent_id)
        ->orderBy('display_order','asc')
        ->select('region_id','region_name','parent_id','level')
        ->get();
        if(count($result)<1){
            return response()->json(['status'=>0,'errmsg'=>'data not found'],404);
        }
        return response()->json(['status'=>1,'data'=>$result]);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $contact = [
            0 => [
                'contact_type'=>0,
                'user_name'=>$data['lianxiren_0_user_name'],
                'danwei_name' => $data['lianxiren_0_danwei_name'],
                'phone' => $data['lianxiren_0_phone'],
                'native_id' => $data['lianxiren_0_native_id'],
                'address_1' => $data['lianxiren_0_address_1'],
                'address_2' => $data['lianxiren_0_address_2'],
                'address_3' => $data['lianxiren_0_address_3'],
            ],
            1 => [
                'contact_type'=>1,
                'user_name'=>$data['lianxiren_1_user_name'],
                'danwei_name' => $data['lianxiren_1_danwei_name'],
                'phone' => $data['lianxiren_1_phone'],
                'native_id' => $data['lianxiren_1_native_id'],
                'address_1' => $data['lianxiren_1_address_1'],
                'address_2' => $data['lianxiren_1_address_2'],
                'address_3' => $data['lianxiren_1_address_3'],
            ]
        ];
        if($data['enroll_id']){
            foreach ($contact as $key => $item) {
                DB::table("base_school_enroll_contact")
                    ->where('contact_id',$item['lianxiren_'.($key+1)])
                    ->update($item);
            }
            $unset_key = [
                'lianxiren_0_user_name',
                'lianxiren_0_danwei_name',
                'lianxiren_0_phone',
                'lianxiren_0_native_id',
                'lianxiren_0_address_1',
                'lianxiren_0_address_2',
                'lianxiren_0_address_3',
                'lianxiren_1_user_name',
                'lianxiren_1_danwei_name',
                'lianxiren_1_phone',
                'lianxiren_1_native_id',
                'lianxiren_1_address_1',
                'lianxiren_1_address_2',
                'lianxiren_1_address_3'
            ];
            foreach ($unset_key as $d) {
                unset($data[$d]);
            }
            DB::table("base_school_enroll_student")
                ->where("enroll_id",$data['enroll_id'])
                ->where('open_id',$data['open_id'])
                ->update($data);
            return response()->json(['status'=>1,'user_no'=>$data['user_no']]);
        }
        $contact = [
            0 => [
                'contact_type'=>0,
                'user_name'=>$data['lianxiren_0_user_name'],
                'danwei_name' => $data['lianxiren_0_danwei_name'],
                'phone' => $data['lianxiren_0_phone'],
                'native_id' => $data['lianxiren_0_native_id'],
                'address_1' => $data['lianxiren_0_address_1'],
                'address_2' => $data['lianxiren_0_address_2'],
                'address_3' => $data['lianxiren_0_address_3'],
                ],
            1 => [
                'contact_type'=>1,
                'user_name'=>$data['lianxiren_1_user_name'],
                'danwei_name' => $data['lianxiren_1_danwei_name'],
                'phone' => $data['lianxiren_1_phone'],
                'native_id' => $data['lianxiren_1_native_id'],
                'address_1' => $data['lianxiren_1_address_1'],
                'address_2' => $data['lianxiren_1_address_2'],
                'address_3' => $data['lianxiren_1_address_3'],
            ]
        ];
        foreach ($contact as $key => $item) {
            $contact_id = Uuid::generate(1);
            $item['contact_id'] = $contact_id->string;
            DB::table("base_school_enroll_contact")->insert($item);
            $data['lianxiren_'.$key+1] = $item['contact_id'];
        }
        $unset_key = [
            'lianxiren_0_user_name',
            'lianxiren_0_danwei_name',
            'lianxiren_0_phone',
            'lianxiren_0_native_id',
            'lianxiren_0_address_1',
            'lianxiren_0_address_2',
            'lianxiren_0_address_3',
            'lianxiren_1_user_name',
            'lianxiren_1_danwei_name',
            'lianxiren_1_phone',
            'lianxiren_1_native_id',
            'lianxiren_1_address_1',
            'lianxiren_1_address_2',
            'lianxiren_1_address_3'
        ];
        foreach ($unset_key as $d) {
            unset($data[$d]);
        }
        $string = Uuid::generate(1);
        $data['enroll_id'] = $string->string;
        $year = Carbon::today()->year;
        $data['user_no'] = Predis::hincrby("base_school_enroll_$year",$data['channel_id'],1);
        if(DB::table("base_school_enroll_student")->insert($data)){
            return response()->json(['status'=>1,'user_no'=>$data['user_no']]);
        }else{
            Predis::hincrby("base_school_enroll_$year",$data['channel_id'],-1);
            return response()->json(['status'=>0,'errmsg'=>'failed']);
        }
    }

    public function show($open_id)
    {

        $result = DB::table("base_school_enroll_student")->where("open_id",$open_id)->get();

        return response()->json(['status'=>1,'data'=>$result]);
    }

    public function save(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input,[
            'enroll_id' => 'required',
            'open_id' => 'required'
        ]);

        if($validator->failed()){
            return response()->json(['status'=>0,'errmsg'=>'参数不正确'],403);
        }

        $res = DB::table("base_school_enroll_student")
            ->where("enroll_id",$input['enroll_id'])
            ->where('open_id',$input['open_id'])
            ->update($input);

        return response()->json(['status'=>1]);
    }
}
