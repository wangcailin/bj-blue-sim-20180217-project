<?php

namespace app\common\controller;

use think\Controller;
use jssdk\JSSDK;

/**
 * 前台控制器基类
 */
class Frontend extends Controller
{

    public function _initialize()
    {
        parent::_initialize();

        $this->jssdk = new JSSDK("wx6df60d01cc2e0ab3", "c70eda9f0f8efbd6010a264661b1188a");

        if($_GET['siemens_openid']){

            setcookie('siemens_openid',$_GET['siemens_openid'],$this->config->time+86400*12,$this->config->cookiePath,$this->config->domain,$this->config->cookieSecure);
            header("Location:".str_replace('&siemens_openid='.$_GET['siemens_openid'],'',str_replace('?siemens_openid='.$_GET['siemens_openid'],'',$this->curPageURL())));
            die();
        }

        $this->openid=$_COOKIE['siemens_openid'];

        if($_GET['sourceOpenid']){
            setcookie('sourceOpenid',$_GET['sourceOpenid'],$this->config->time+86400*12,$this->config->cookiePath,$this->config->domain,$this->config->cookieSecure);
            header("Location:".str_replace('&sourceOpenid='.$_GET['sourceOpenid'],'',str_replace('?sourceOpenid='.$_GET['sourceOpenid'],'',$this->curPageURL())));
            die();
        }
        $this->sourceOpenid=$_COOKIE['sourceOpenid'];
        if(!$this->openid){
            setcookie('state',$this->curPageURL(),$this->config->time+86400*30,$this->config->cookiePath,$this->config->domain,$this->config->cookieSecure);
            header("Location:https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx6df60d01cc2e0ab3&redirect_uri=".urlencode("http://www.wechat.siemens.com.cn/external/getOpenid4.php")."&response_type=code&scope=snsapi_base&state=".urlencode($this->curPageURL())."#wechat_redirect");

            die("请从微信点击菜单入口进入");
        }

        if(!$this->sourceOpenid){
            $this->sourceOpenid = $this->openid;
        }

        $signPackage = $this->jssdk->getSignPackage($_GET["requrl"]);
        $this->template->assign('signPackage',$signPackage);
        $this->template->assign('blueopenid',$this->openid);
    }

    public function curPageURL()
    {
        $pageURL = 'http';

        if ($_SERVER["HTTPS"] == "on")
        {
            $pageURL .= "s";
        }
        $pageURL .= "://";

        if ($_SERVER["SERVER_PORT"] != "80")
        {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        }
        else
        {
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }

}
