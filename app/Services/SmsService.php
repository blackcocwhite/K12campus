<?php
/**
 * Created by PhpStorm.
 * User: Jh
 * Date: 2017/6/1
 * Time: 14:32
 */

namespace App\Services;

use SmsManager;

class SmsService
{
    private $sms;

    public function __construct(SmsManager $sms)
    {
        $this->sms = $sms;
    }

}
