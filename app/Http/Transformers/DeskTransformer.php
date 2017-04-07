<?php

namespace App\Http\Transformers;

/**该类为dingo api封装好**/
use League\Fractal\TransformerAbstract;

class DeskTransformer extends TransformerAbstract
{
    /***
     * 分开为了解耦
     * 数据字段选择
     * @param $lesson
     * @return array
     */
    public function transform($lesson)
    {
        dd($lesson);
        /******隐藏数据库字段*****/
        return [
            'common_url' => $lesson['user_name'],
            'article_img' => $lesson['user_email'],
        ];
    }
}
