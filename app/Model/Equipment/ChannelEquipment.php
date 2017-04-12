<?php

namespace App\Model\Equipment;

use Illuminate\Database\Eloquent\Model;

class ChannelEquipment extends Model
{
    protected $table = 'app_jiaozhuang_channel_equipment';
    protected $primaryKey = 'channel_equipment_id';

    public function orders()
    {
        return $this->hasMany('App\Model\Equipment\Order');
    }
}
