<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SmsManager;
use Validator;

class SmsController extends Controller
{
    public function validateCode(Request $request)
    {

        //验证数据
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|confirm_mobile_not_change|confirm_rule:mobile_required',
            'verifyCode' => 'required|verify_code',
        ]);
        if ($validator->fails()) {
            //验证失败后建议清空存储的发送状态，防止用户重复试错
            SmsManager::forgetState();
            return array('status' => 0);
        }
        return array('status' => 1);
    }
}
