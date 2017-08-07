<?php

/**
 * Created by PhpStorm.
 * User: Jh
 * Date: 2017/4/14
 * Time: 10:17
 */
namespace App\Repositories;

use Predis;
use Uuid;

class UserRepository
{
    /**
     * 检测用户是否注册
     * @param $openid
     * @return array
     */
    public function checkRegister($uid,$openid,$wappid='wxde252df044180329')
    {
        if($user_id = Predis::hget("WechatUserWithUnionId:$uid",'userId')){
            Predis::hset("WechatUserWithUnionId:$uid",$wappid,$openid);
            Predis::hset("user:$user_id",$wappid,$openid);
            $mobile = Predis::hget("user:$user_id",'mobile');
            return array('status'=>1,'data'=>array('userId'=>$user_id,'mobile'=>$mobile));
        }else{
            return array('status'=>0);
        }
    }
    /**
     * 系统注册
     */
    public function systemRegister($uid,$openid,$mobile,$wappid='wxde252df044180329')
    {
        if(Predis::exists("app.user:$mobile")){
            $user_id = Predis::hget("app.user:$mobile","userId");
            if(Predis::exists("WechatUserWithUnionId:$uid")){
                return array('status'=>0,'errmsg'=>'该微信号已经绑定过！');
            }else{
                Predis::pipeline(function ($pipe) use($uid,$openid,$user_id,$mobile,$wappid){
                    $pipe->hmset("WechatUserWithUnionId:$uid",
                        array('userId' => $user_id, $wappid => $openid)
                    )
                        ->hset("user:$user_id",$wappid,$openid)
                        ->sadd("sync.user.list",$user_id);
                });
                $_keys = ["WechatUserWithUnionId:$uid","user:$user_id"];
                return array('status'=>1,'data'=>array('userId'=>$user_id,'_keys'=>$_keys));
            }
        }else{
            $string = Uuid::generate(1);
            $user_id = $string->string;

            $userInfo = $this->getSubscribeUserInfo($openid);
            $avatar = isset($userInfo['headimgurl']) ? $userInfo['headimgurl'] : '';
            $displayName = isset($userInfo['nickname']) ? $userInfo['nickname'] : '未关注用户';
            $sex = isset($userInfo['sex']) == 1 ? 0 : 1;

            Predis::pipeline(function ($pipe) use($uid,$openid,$mobile,$wappid,$user_id,$sub,$avatar,$displayName,$sex){
               $pipe->hmset("WechatUserWithUnionId:$uid",
                   array('userId'=>$user_id,$wappid=>$openid)
               )
                   ->hmset("user:$user_id",array('userId' => $user_id, $wappid => $openid, 'mobile' => $mobile, 'avatar' => $avatar, 'displayName' => $displayName,'sex' => $sex))
                   ->hmset("app.user:$mobile",array('userId' => $user_id, 'mobile' => $mobile))
                   ->sadd("sync.user.list",$user_id);
            });
            $_keys = ["WechatUserWithUnionId:$uid","user:$user_id","app.user:$mobile"];
            return array('status'=>1,'data'=>array('userId'=>$user_id,'_keys'=>$_keys));
        }
    }

    /**
     * 获取关注用户的基本信息
     * @param $openid
     * @return mixed
     */
    public function getSubscribeUserInfo($openid)
    {
        return Predis::hgetall("_uid($openid)");
    }

    /**
     * 修改用户信息
     * @param $uid
     * @param $type
     * @param $data
     */
    public function _modifyUser($uid,$type,$data)
    {
        if($user_id = Predis::hget("WechatUserWithUnionId:$uid",'userId')){
            Predis::hset("user:$user_id",$type,$data);
        }
    }

    public function getStudent($user_id,$channel_id)
    {
        $list = Predis::sMembers("userType:$channel_id:$user_id");
    }

    public function getGroup($user_id,$channel_id)
    {
        $list = Predis::sMembers("userType:$channel_id:$user_id");
        $data = [];
        if(! empty($list)){
            foreach ($list as $key => $item) {
                $cache = explode(':',$item);
                $data[$key]['group_id'] = $cache[0];
                $data[$key]['user_type'] = $cache[1];
                if($cache[1] == 1){
                    $data[$key]['student_name'] = Predis::hGet("group.member:$cache[0]:$user_id","studentName");
                    $data[$key]['student_id'] = Predis::hget("group.member:$cache[0]:$user_id","studentId");
                }
            }
        }
        return $data;

    }
}
