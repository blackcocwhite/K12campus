<?php

namespace App\model\Equipment;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'app_jiaozhuang_repaire_user';
    protected $primaryKey = 'repaire_user_id';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = ['createAt','updateAt'];

}
