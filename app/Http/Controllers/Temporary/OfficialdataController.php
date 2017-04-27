<?php

namespace App\Http\Controllers\Temporary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Model\Temporary\Officialdata;

class OfficialdataController extends Controller
{
    protected $fields = [
        'city' => '',
        'province' => '',
        'region' => '',
        'type' => '',
        'school_name' => '',
        'operator_name' => '',
        'operator_phone' => '',
        'operator_email' => '',
        'operator_email_password' => '',
        'operator_id_number' => '',
        'organizing_code' => '',
        'representative' => '',
        'account_name' => '',
        'deposit_bank' => '',
        'bank_account' => '',
        'missive_img' => '',
        'legal_img' => '',
        'address' => '',
        'is_auth' => '',
        'is_connect' => '',
        'has_official_account' => ''
    ];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datas = Officialdata::all();
        return view('temporary.questionnaire.index')->withDatas($datas);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $official = new Officialdata();
        foreach (array_keys($this->fields) as $field) {
            $official->$field = $request->get($field);
        }
        $official->save();
        return array('status'=>1);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $datas = Officialdata::findOrFail($id);
        $data = ['id' => $id];
        foreach (array_keys($this->fields) as $field) {
            $data[$field] = old($field, $datas->$field);
        }

        return view('temporary.questionnaire.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

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
        $data = new Officialdata();
//        $data->is_connect = $request->get('is_connect');
        $data->where('id',$id)->update(['is_connect'=>$request->get('is_connect')]);
        return redirect('/temporary/questionnaire/index')
            ->withSuccess("The school '$data->school_name' was connected.");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = Officialdata::findOrFail($id);
        $data->delete();

        return redirect('/temporary/questionnaire/index')
            ->withSuccess("The '$data->school_name' tag has been deleted.");
    }
}
