<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Test;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Uuid;
use Predis;
class TestController extends Controller
{
    protected $fields = [
//        'oa' => '',
//        'index' => '',
//        'time' => '',
        'da' => '',
        'rssi' => '',
        'us' => '',
        'step' => '',
        'mfd' => '',
//        'create_time' =>  '',
    ];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
//        $ds_list = $request->get('ds');
//        $now = Carbon::now();
//        $data = json_encode($request->all());
//        Log::info($data);
//        die;
//        foreach ($ds_list as $item) {
//
//            if(Predis::hsetnx("mac:$item[da]",$request->get('oa'),1)){
//                foreach (array_keys($this->fields) as $field) {
//                    $data['data_'.$field] = $item[$field];
//                }
//                $uuid = Uuid::generate(1);
//                $data['data_id'] = $uuid->string;
//                $data['data_oa'] = $request->get('oa');
//                $data['data_index'] = $request->get('index');
//                $data['createdAt'] = $now;
//                $data['updatedAt'] = $now;
//                $data['create_time'] = $now;
//                $data['data_time'] = $request->get('time');
//                Test::create($data);
//                Predis::expire("mac:$item[da]",'300');
//            }

//        }
        return array('status'=>1);
//        if(Predis::hsetnx("mac:$da",$request->get('oa'),1)){
//            foreach (array_keys($this->fields) as $field) {
//                $data['data_'.$field] = $request->get($field);
//            }
//
//            $data['createdAt'] = $now;
//            $data['updatedAt'] = $now;
//            $data['create_time'] = $now;
//            Test::create($data);
//            Predis::expire("mac:$da",'300');
//            return array('status'=>1);
//        }else{
//            return array('status'=>0);
//        }


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
