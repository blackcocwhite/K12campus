<?php

namespace App\Model\Equipment;

use Illuminate\Database\Eloquent\Model;

class OrderPlace extends Model
{
    protected $table = 'app_jiaozhuang_repaire_place';
    protected $primaryKey = 'repaire_place_id';

    public function order()
    {
        return $this->belongsTo('App\Model\Eequipment\Order','repaire_place_id','order_id');
    }
}
