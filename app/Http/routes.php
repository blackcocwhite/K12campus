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
/****系统注册****/
Route::get('/checkUser/{openid}',"UserController@login");
Route::get('/systemRegister/{openid}/{mobile}',"UserController@create");

/****教育装备客服系统****/
Route::post('/doRegister',"Equipment\UserController@postRegister");//如果是负责人直接注册 则直接关联

/****教育装备客服系统----报修端****/
Route::group(['namespace' => 'Equipment','middleware' => 'wechat.user'], function () {
    Route::get('/teacher',"OrderController@repairOrderList");
    Route::get('/order/{order_id}',"OrderController@orderInformation");
    Route::post('/create',"OrderController@create");
    Route::get('/login',"UserController@login");
    Route::post('/checkAccendant',"UserController@associateRepairer");

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
