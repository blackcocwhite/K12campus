<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::any('/',function(){
    return view('welcome');
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
});
Route::group( ['namespace' => "Wechat"], function () {
    Route::any( '/wechat', 'WechatController@serve' );
    Route::get( '/oauth/cid/{cid}/fid/{fid}/wappid/{wappid}', 'WechatController@oauth' );
    Route::get( '/oauth_callback', 'WechatController@oauth_callback' );
} );
//http://www.8dsun.com/oauth.php?cid=8aadb31e5dc16dd8015dc4a2191d01dc&fid=CHANNELID&wappid=wxde252df044180329
