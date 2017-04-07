<?php
namespace App\Http\Controllers\Api;
use Predis;
use App\Http\Transformers\DeskTransformer;
class DeskController extends BaseController
{
    public function index($channelId)
    {
        $articleList = json_decode(Predis::get('_b_school_desktop_article:'.$channelId),true);
        if(empty($articleList)){
            return $this->response->errorNotFound('数据没有找到');
        }
        $data = array();
        foreach ($articleList as $key => $value) {
            $data['article'][$key] = $value;
            switch($value['code']){
                case 'notice':
                    $data['article'][$key]['url'] = 'http://'.$_SERVER['HTTP_HOST'].'/WebApp/appNoticeCenter/#/noticeDetail/'.$value['article_id'];
                    break;
                case 'info':
                    $data['article'][$key]['url'] = 'http://'.$_SERVER['HTTP_HOST'].'/WebApp/appSchInfo/#/oneInfo/'.$value['article_id'];
                    break;
                case 'parentMeeting':
                    $data['article'][$key]['url'] = 'http://'.$_SERVER['HTTP_HOST'].'/WebApp/app_parent_meeting/client/#/video/'.$value['article_id'];
                    $webcastId = Predis::hGet('parent.meeting:' . $value['article_id'],'webcastId' );
                    $data['article'][$key]['state'] = Predis::hGet('webcast:'.$webcastId,'state');
                    break;
                case 'activity':
                    $data['article'][$key]['url'] = 'http://'.$_SERVER['HTTP_HOST'].'/WebApp/app_activity/client/?path=/actIndex&aid='.$value['article_id'];
                    $data['article'][$key]['commentNum'] = Predis::sCard('app_activity_aid('.$value['article_id'].')_eidx_createTime_not_deleted');
                    $data['article'][$key]['star'] =  Predis::get('_app_activity_aid('.$value['article_id'].')_star') ? : 0;
                    $data['article'][$key]['signNum'] = Predis::zCard('_app_activity_aid('.$value['article_id'].')_uidx_signTime');
                    $data['article'][$key]['tag'] = Predis::hGet('_app_activity_aid('.$value['article_id'].')','tags');
                    break;
            }
        }
        dd($data);
        return $this->item($data, new DeskTransformer());
    }
}
