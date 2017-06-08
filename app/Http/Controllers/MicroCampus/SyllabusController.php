<?php
/**
 * Created by PhpStorm.
 * User: Jh
 * Date: 2017/6/8
 * Time: 16:12
 */

namespace App\Http\Controllers\MicroCampus;

use App\Http\Controllers\Controller;
use Predis;

class SyllabusController extends Controller
{
    public function index($channel_id,$term)
    {
        $list = Predis::sMembers("userType:$channel_id:$_SERVER[HTTP_AUTHORIZATION]");
        $data = [];
        foreach ($list as $key => $item) {
            $_cache = explode(':', $item);
            if ($_cache[1] == 1) {
                $data['group_id'][$key] = $_cache[0];
            }
        }
        if (empty($data)) return array('status' => 0, 'errmsg' => "no data");
        $result = Predis::hget('_b_school_term_course_'.$channel_id,$term.'_'.$data['group_id'][0]);
        $result = json_decode($result,true);
        $result = json_decode($result['course'],true);
        return $result;
    }
}
