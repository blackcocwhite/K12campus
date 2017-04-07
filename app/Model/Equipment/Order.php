<?php

namespace App\Model\Equipment;
use Illuminate\Database\Eloquent\Model;

class Order extends Model{

    protected $table = 'app_jiaozhuang_order';

    protected $primaryKey = 'order_id';

    public $timestamps = false;

    protected $guarded = ['is_point','is_visit','state','receive_status'];

    public function images()
    {
        return $this->hasMany('App\Model\Eequipment\OrderImg');
    }

    public function schedules()
    {
        return $this->hasMany('App\Model\Eequipment\OrderSchedule');
    }

    public function places()
    {
        return $this->hasMany('App\Model\Eequipment\OrderPlace');
    }

    /**
     * 待接单的工单
     * @param $query
     * @return mixed
     */
    public function scopePending($query)
    {
        return $query->where('state', 2)->where('receive_status',0);
    }
    /**
     * 已接单的工单
     * @param $query
     * @return mixed
     */
    public function scopeHanding($query)
    {
        return $query->where('state', 2)->where('receive_status',1);
    }
    /**
     * 已完成的工单
     * @param $query
     * @return mixed
     */
    public function scopeComplete($query)
    {
        return $query->where('state', 3)->where('receive_status',1);
    }
}
