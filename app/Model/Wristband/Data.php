<?php

namespace App\Model\Wristband;

use Illuminate\Database\Eloquent\Model;

class Data extends Model
{
    protected $table = 'app_shouhuan_data';
    protected $primaryKey = 'data_id';
//    public $timestamps = false;
//    protected $guarded = ['is_point','is_visit','state','receive_status'];
//    public $incrementing = false;

    public function student()
    {
        return $this->belongsTo('App\Model\Wristband\Student', 'da_id');
    }
}
