<?php

namespace App\Model\Equipment;

class OrderSchedule extends BaseModel
{
    protected $table = 'app_jiaozhuang_schedule';
    protected $primaryKey = 'schedule_id';
    protected $guarded = ['createAt','updateAt'];
    public $timestamps = false;

    public function order()
    {
        return $this->belongsTo('App\Model\Eequipment\Order');
    }

    public function scopeOne($q)
    {
        return $q->take(1);
    }

}
