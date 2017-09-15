<?php

namespace App\Http\Controllers\Wechat;

use Log;
use EasyWeChat;
use App\Http\Controllers\Controller;

class WechatController extends Controller
{

    /**
     * 处理微信的请求消息
     *
     * @return string
     */
    public function serve()
    {
        $wechat = app('wechat');
        $wechat->server->setMessageHandler(function($message){
            switch ($message->MsgType) {
                case 'event':
                    return '收到事件消息';
                    break;
                case 'text':
                    return '收到文字消息';
                    break;
                case 'image':
                    return '收到图片消息';
                    break;
                case 'voice':
                    return '收到语音消息';
                    break;
                case 'video':
                    return '收到视频消息';
                    break;
                case 'location':
                    return '收到坐标消息';
                    break;
                case 'link':
                    return '收到链接消息';
                    break;
                // ... 其它消息
                default:
                    return '收到其它消息';
                    break;
            }
        });
        Log::info('return response.');

        return $wechat->server->serve();
    }

    public function oauth()
    {
        $url = "/oauth_callback";
        config( ['wechat.oauth.callback' => $url] );

        $wechat = app( 'wechat' );
        $oauth = $wechat->oauth;
        return $oauth->redirect();
//// 未登录
//        if (empty($_SESSION['wechat_user'])) {
//            $_SESSION['target_url'] = 'user/profile';
//            return $oauth->redirect();
//            // 这里不一定是return，如果你的框架action不是返回内容的话你就得使用
//            // $oauth->redirect()->send();
//        }
    }

    public function oauth_callback()
    {
        $wechat = app( 'wechat' );
        $oauth = $wechat->oauth;
        $user = $oauth->user();
        echo "<pre>";
        print_r( $user );
    }
}
