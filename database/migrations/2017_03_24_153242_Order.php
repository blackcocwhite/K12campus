<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Order extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_jiaozhuang_order', function (Blueprint $table) {
            $table->string('order_id');
            $table->string('order_no');
            $table->string('state');
            $table->text('content');
            $table->timestamps('create_time');
            $table->timestamp('published_at')->index();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
