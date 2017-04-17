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

// Route::get('/login/{user_id}','EequipmentController@login');
// Route::get('/checkAccendant/{user_id}/{mobile}','EequipmentController@checkAccendant');
// Route::get('/checkHead/{user_id}/{mobile}','EequipmentController@checkHead');

// Route::get('/pendingOrder/{user_id}','EequipmentController@pendingOrder');
// Route::get('/handingOrder/{user_id}','EequipmentController@handingOrder');
// Route::get('/completeOrder/{user_id}','EequipmentController@completeOrder');
// Route::get('/evaluatedOrder/{user_id}','EequipmentController@evaluatedOrder');

// Route::post('/receiveOrder','EequipmentController@receiveOrder');
// Route::post('/confirmVisit','EequipmentController@confirmVisit');
// Route::post('/addSchedule','EequipmentController@addSchedule');
// Route::post('/addPoint','EequipmentController@addPoint');
// Route::post('/deletePoint','EequipmentController@deletePoint');
// Route::post('/confirmComplete','EequipmentController@confirmComplete');
// Route::get('/getOrder/{order_id}','EequipmentController@getOrder');

// Route::post('/createOrder','EequipmentController@createOrder');

// Route::get('/orderList/{user_id}','EequipmentController@orderList');
// Route::post('/evaluate','EequipmentController@evaluate');

/****系统注册****/
Route::get('/checkUser/{openid}',"UserController@login");
Route::get('/systemRegister/{openid}/{mobile}',"UserController@create");

/****教育装备客服系统****/
Route::post('/doRegister',"Equipment\UserController@postRegister");//如果是负责人直接注册 则直接关联

Route::group(['prefix'=>'api','namespace' => 'Equipment','middleware' => 'wechat.user'], function () {
    Route::get('/teacher',"OrderController@repairOrderList");
    Route::get('/order/{order_id}',"OrderController@orderInformation");
    Route::post('/create',"OrderController@create");
    Route::get('/login',"UserController@login");
    Route::post('/checkAccendant',"UserController@associateRepairer");
});

//Route::group(['namespace' => 'Equipment','middleware' => 'equipmentAuth'], function () {
Route::group(['namespace' => 'Equipment'], function () {
    Route::get('/pendingOrderList',"OrderController@pendingOrderList");
});
