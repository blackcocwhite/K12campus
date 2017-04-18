<?php

namespace App\Model\Equipment;

use Illuminate\Database\Eloquent\Model;

class OrderSchedule extends Model
{
    protected $table = 'app_jiaozhuang_schedule';
    protected $primaryKey = 'schedule_id';
    protected $guarded = ['createAt','updateAt'];
    public $timestamps = false;

    public function order()
    {
        return $this->belongsTo('App\Model\Eequipment\Order');
    }
}
