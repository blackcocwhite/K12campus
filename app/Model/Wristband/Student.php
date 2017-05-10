<?php

namespace App\Model\Wristband;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'app_shouhuan_da_student';
    protected $primaryKey = 'channel_da_student_id';

    public function datas()
    {
        return $this->hasMany('App\Model\Wristband\Data', 'data_id');
    }
}
