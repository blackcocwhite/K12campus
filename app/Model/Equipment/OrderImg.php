<?php

namespace App\Model\Equipment;
use Illuminate\Database\Eloquent\Model;

class OrderImg extends Model
{
    protected $table = 'app_jiaozhuang_img';
    protected $primaryKey = 'img_id';
    /**
     * 获取拥有此电话的用户。
     */
    public function order()
    {
        return $this->belongsTo('App\Model\Eequipment\Order','img_id','order_id');
    }
}