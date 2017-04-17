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
    public function checkRegister($openid)
    {
        if($user_id = Predis::hget("wechat.user:$openid",'userId')){
            $mobile = Predis::hget("user:$user_id",'mobile');
            return array('status'=>1,'data'=>array('userId'=>$user_id,'mobile'=>$mobile));
        }else{
            return array('status'=>0);
        }
    }
    /**
     * 系统级注册
     */
    public function systemRegister($openid,$mobile,$wappId='wxde252df044180329')
    {
        if(Predis::exists("app.user:$mobile")){
            $user_id = Predis::hget("app.user:$mobile","userId");
            if(Predis::exists("wechat.user:$openid")){
                return array('status'=>0,'errmsg'=>'该微信号已经绑定过！');
            }else{
                Predis::pipeline(function ($pipe) use($openid,$user_id,$mobile,$wappId){
                    $pipe->hmset("wechat.user:$openid", array('userId' => $user_id, 'openId' => $openid,'wappId'=> $wappId))
                        ->hset("user:$user_id",$wappId,$openid)
                        ->sadd("sync.user.list",$user_id);
                });
                $_keys = ["wechat.user:$openid","user:$user_id"];
                return array('status'=>1,'data'=>array('userId'=>$user_id,'_keys'=>$_keys));
            }
        }else{
            $string = Uuid::generate(1);
            $user_id = $string->string;

            $userInfo = $this->getSubscribeUserInfo($openid);
            $sub = isset($userInfo['subscribe']) ? $userInfo['subscribe'] : 0;
            $avatar = isset($userInfo['headimgurl']) ? $userInfo['headimgurl'] : '';
            $displayName = isset($userInfo['nickname']) ? $userInfo['nickname'] : '未关注用户';
            $sex = isset($userInfo['sex']) == 1 ? 0 : 1;

            Predis::pipeline(function ($pipe) use($openid,$mobile,$wappId,$user_id,$sub,$avatar,$displayName,$sex){
               $pipe->hmset("wechat.user:$openid",array('userId'=>$user_id,'openId'=>$openid,'wappId'=>$wappId))
                   ->hmset("user:$user_id",array('userId' => $user_id, $wappId => $openid, 'mobile' => $mobile, 'avatar' => $avatar, 'displayName' => $displayName,'subscribe' => $sub,'sex' => $sex))
                   ->hmset("app.user:$mobile",array('userId' => $user_id, 'mobile' => $mobile))
                   ->sadd("sync.user.list",$user_id);
            });
            $_keys = ["wechat.user:$openid","user:$user_id","app.user:$mobile"];
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

    public function _modifyUser($openid,$type,$data)
    {
        if($user_id = Predis::hget("wechat.user:$openid",'userId')){
            Predis::hset("user:$user_id",$type,$data);
        }
    }
}