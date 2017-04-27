<?php

namespace App\Model\Equipment;

class Order extends BaseModel
{

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

    public function scopeOfType($query, $state, $receive_status)
    {
        return $query->where('state',$state)->where('receive_status',$receive_status);
    }

    /**
     * Get latest 5 comments from hasMany relation.
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function latestSchedules()
    {
        return $this->schedules()->orderBy('create_time', 'desc')->nPerGroup('order_id', 1);
    }

}
