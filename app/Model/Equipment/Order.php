<?php

namespace App\Model\Equipment;
use Illuminate\Database\Eloquent\Model;

class Order extends Model{

    protected $table = 'app_jiaozhuang_order';
    protected $primaryKey = 'order_id';
    public $timestamps = false;
    protected $guarded = ['is_point','is_visit','state','receive_status'];
    public $incrementing = false;

    public function images()
    {
        return $this->hasMany('App\Model\Equipment\OrderImg');
    }

    public function schedules()
    {
        return $this->hasMany('App\Model\Equipment\OrderSchedule');
    }

    public function places()
    {
        return $this->hasMany('App\Model\Equipment\OrderPlace');
    }

    public function equipments()
    {
        return $this->belongsTo('App\Model\Equipment\ChannelEquipment');
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

    public function scopeOfType($query,$state,$receive_status){
        return $query->where('state',$state)->where('receive_status',$receive_status);
    }
}
