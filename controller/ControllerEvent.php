<?php
	/**
	 *
	 * 用户控制器
	 *
	 * @category Controller
	 * @package Controller
	 * @author harry
	 * @version v1.0 2014-06-23
	 */
	class ControllerEvent extends Base
	{
		/**
		 * 构造函数
		 *
		 * @access public
		 */
		public function __construct()
		{
			parent::Base();

			include_once($this->config->modelDir.'ModelEvent.php');
			$this->model = new ModelEvent();

			self::initConfig();
			if(!isset($_SESSION)){ //判断session是否开启
				session_start(); //开启就session
			} 
			
			include_once("../jssdk2.php");
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
			//var_dump($token);
			$this->template->assign('signPackage',$signPackage);
			$this->template->assign('blueopenid',$this->openid);

		}
		
		/**
		 * 构造函数
		 *
		 * @access public
		 */
		public function ControllerEvent()
		{
			self::__construct();
		}

		/**
		 * 首页
		 *
		 * @access public
		 */
		public function getuserinfo(){
			
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

		public function index()
		{
			$this->template->display('index.html');
		}

		public function winover()
		{
			$sql="select count(*) nub,type from sim_katongwin where  openid='".$this->openid."' group by type ";
			$mytypecount=$this->db->loadToArray($sql);
			if($mytypecount){
				foreach($mytypecount as $key=>$value){
					if($value['nub']>=1){
						$this->template->assign('type'.$value['type'],1);
					}
				}
			}
			$this->template->display('select.html');
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
		
	}
?>