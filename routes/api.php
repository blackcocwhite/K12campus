<?php

Route::group(['prefix'=>'v1'], function () {
    /****系统注册****/
    Route::get('/checkUser/{openid}/{unionid}/{wappid}',"UserController@login");
    Route::post('/systemRegister',"UserController@create");

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
        Route::get( '/checkjyj/{mobile}', "UserController@check_jyj" );
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
        Route::get('/channel_term_info/{channel_id}/{term}', "WristbandController@channel_term_info");
    });

    /*****微校园应用*******/
    Route::group(['namespace' => 'MicroCampus', 'prefix' => 'microCampus', 'middleware' => 'wechat.user'],function () {
        Route::get('/syllabus/{channel_id}/{term}',"SyllabusController@index");
        Route::get('/resultList/{channel_id}',"ResultController@index");
        Route::get('/resultInfo/{exam_id}/{student_id}',"ResultController@show");
        Route::post('/statistics',"ResultController@statistics");
    });

    Route::group(['namespace' => 'MicroCampus', 'prefix' => 'microCampus'], function () {
        Route::get('/voteList/{channel_id}/{page}', 'VoteController@index');
        Route::post('/vote', 'VoteController@show');
        Route::post('/doVote', 'VoteController@store');
        Route::post('/voteStatistics',"VoteController@statistics");

        Route::get('/recruitment/{channel_id}',"RecruitController@index");
        Route::get('/nation',"RecruitController@getNation");
        Route::get('/region/{parent_id}',"RecruitController@getRegion");
        Route::post('/recruit',"RecruitController@store");
        Route::get('/recruit/{open_id}',"RecruitController@show");

    });
});
