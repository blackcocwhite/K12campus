<?php

namespace App\Model\Temporary;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    protected $table = 'app_shouhuan_data';
    protected $primaryKey = 'data_id';
    public $timestamps = false;
    protected $guarded = [];
    public $incrementing = false;
}
