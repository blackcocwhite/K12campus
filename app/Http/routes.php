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

Route::get('/login/{user_id}','EequipmentController@login');
Route::get('/checkAccendant/{user_id}/{mobile}','EequipmentController@checkAccendant');
Route::get('/checkHead/{user_id}/{mobile}','EequipmentController@checkHead');

Route::get('/pendingOrder/{user_id}','EequipmentController@pendingOrder');
Route::get('/handingOrder/{user_id}','EequipmentController@handingOrder');
Route::get('/completeOrder/{user_id}','EequipmentController@completeOrder');
Route::get('/evaluatedOrder/{user_id}','EequipmentController@evaluatedOrder');

Route::post('/receiveOrder','EequipmentController@receiveOrder');
Route::post('/confirmVisit','EequipmentController@confirmVisit');
Route::post('/addSchedule','EequipmentController@addSchedule');
Route::post('/addPoint','EequipmentController@addPoint');
Route::post('/confirmComplete','EequipmentController@confirmComplete');
Route::get('/getOrder/{order_id}','EequipmentController@getOrder');

Route::post('/createOrder','EequipmentController@createOrder');

Route::get('/orderList/{user_id}','EequipmentController@orderList');
Route::post('/evaluate','EequipmentController@evaluate');

Route::group(['namespace' => 'Equipment','middleware' => 'wechat.user'], function () {
    Route::get('/teacher',"OrderController@repairOrderList");
    Route::get('/order/{order_id}',"OrderController@orderInformation");
    Route::post('/create',"OrderController@create");
});


// $api = app('Dingo\Api\Routing\Router');
// $api->version('v1', function ($api) {
//     $api->group(['namespace'=>'App\Http\Controllers\Api'],function($api){

//         $api->get('tests', 'TestsController@index');
//         $api->get('desk/{channelId}','DeskController@index');

//     });
// });
