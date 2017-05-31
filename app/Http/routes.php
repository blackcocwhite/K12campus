<?php
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
Route::any('/',function(){
    return view('welcome');
});
Route::group(['prefix'=>'v1'], function () {
    /****系统注册****/
    Route::get('/checkUser/{openid}',"UserController@login");
    Route::get('/systemRegister/{openid}/{mobile}/{wappid}',"UserController@create");

    /****教育装备客服系统****/
    Route::post('/doRegister',"Equipment\UserController@postRegister");//如果是负责人直接注册 则直接关联

    /****教育装备客服系统----报修端****/
    Route::group(['namespace' => 'Equipment','middleware' => 'wechat.user'], function () {
        Route::get('/orderList',"OrderController@repairOrderList"); //报修人员工单列表
        Route::get('/getOrder/{order_id}',"OrderController@orderInformation");
        Route::post('/createOrder',"OrderController@create");

        Route::get('/login',"UserController@login");
        Route::post('/checkAccendant',"UserController@associateRepairer");

        Route::post('/evaluate',"OrderController@evaluate");
    });

    /****教育装备客服系统----维修端****/
    Route::group(['namespace' => 'Equipment','middleware' => 'equipmentAuth'], function () {
        Route::get('/pendingOrder',"OrderController@pendingOrderList");
        Route::get('/handingOrder',"OrderController@handingOrderList");
        Route::get('/completeOrder',"OrderController@completeOrderList");
        Route::get('/evaluatedOrder',"OrderController@evaluatedOrderList");

        Route::post('/receiveOrder',"OrderController@receiveOrder");
        Route::post('/confirmVisit',"OrderController@confirmVisit");
        Route::post('/addPoint',"OrderController@addPoint");
        Route::post('/deletePoint',"OrderController@deletePoint");
        Route::post('/addSchedule',"OrderController@addSchedule");
        Route::post('/confirmComplete',"OrderController@confirmComplete");
        Route::get('/allOrders',"OrderController@allOrders");
    });

    /****校园安全系统----家长端****/
    Route::group(['namespace' => 'Wristband', 'prefix' => 'wristband', 'middleware' => 'wechat.user'], function () {
        Route::post('/notifyList', "WristbandController@notifyList");
        Route::post('/leaveListForParent', "WristbandController@leaveListForParent");
        Route::get('/hasStudent/{channelId}', "WristbandController@hasStudent");
        Route::post('/askLeave', "WristbandController@askLeave");
        Route::post('/leaveListForTeacher', "WristbandController@leaveListForTeacher");
        Route::get('/leaveInfo/{leaveId}', "WristbandController@leaveInfo");
        Route::post('/admitLeave', "WristbandController@admitLeave");
        Route::post('/attendance', "WristbandController@attendance");
        Route::post('/groupInfo', 'WristbandController@groupInfo');
        Route::get('/channel_term_info/{channle_id}', "WristbandController@channel_term_info");
    });
});

/*上传图片*/
Route::post('/upload',"UploadController@index");
/*验证短信验证码*/
Route::post('/validateCode', "SmsController@validateCode");

Route::group(['namespace'=>"Temporary","middleware" => "csrf"],function () {
    Route::get('/temporary/questionnaire/index',"OfficialdataController@index");
    Route::get('/temporary/questionnaire/{id}/show',"OfficialdataController@show");
    Route::put('/temporary/questionnaire/{id}',"OfficialdataController@update");
    Route::delete('/temporary/questionnaire/{id}',"OfficialdataController@destroy");
    Route::get('/list', "TestController@index");
//    Route::get('/student', "TestController@index");
});

Route::any('/wechat', 'WechatController@serve');

Route::group(['middleware' => 'wechat.oauth'], function () {
    Route::get('/abctest', function () {
        $user = session('wechat.oauth_user');
        dd($user);
    });
});
