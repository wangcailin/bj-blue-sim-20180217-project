<?php

namespace app\common\controller;

use think\Controller;
use jssdk\JSSDK;

/**
 * 前台控制器基类
 */
class Frontend extends Controller
{

    public $openid = null;
    public $sourceOpenid = null;
    public $jssdk = null;
    public $config = null;

    public function _initialize()
    {
        parent::_initialize();

        $this->jssdk = new JSSDK("wx6df60d01cc2e0ab3", "c70eda9f0f8efbd6010a264661b1188a");

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
        $this->assign('signPackage',$signPackage);
        $this->assign('blueopenid',$this->openid);
    }

    public function getUserInfo(){
        $token=$this->getAccessToken();
        ob_start();
        $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token={$token}&openid=".$this->openid;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        $out1 = ob_get_contents();
        ob_end_clean();
        $getarr=json_decode($out1,true);
        return $getarr;
    }

    public function getAccessToken() {
        if(time()-filemtime("/www/web/weixin_siemens/external/token1.txt")>1200||file_get_contents("/www/web/weixin_siemens/external/token1.txt")==''){
            ob_start();
            $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx6df60d01cc2e0ab3&secret=c70eda9f0f8efbd6010a264661b1188a';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $result = curl_exec($ch);
            $out1 = ob_get_contents();
            ob_end_clean();
            $getarr=json_decode($out1,true);
            file_put_contents("/www/web/weixin_siemens/external/token1.txt",$getarr['access_token']);
            $token=$getarr['access_token'];
        }else{
            $token=file_get_contents("/www/web/weixin_siemens/external/token1.txt");
        }
        return $token;

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
