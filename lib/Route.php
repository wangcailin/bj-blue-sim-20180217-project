<?php
	/**
	 * 路由模型
	 *
	 * @category Model
	 * @package Core
	 * @author harry
	 * @version v1.0 2011-01-17
	 */
	class Route extends Base
	{
		/**
		 * 构造函数
		 *
		 * @access public
		 */
		public function __constcut()
		{
		}
		
		/**
		 * 构造函数
		 *
		 * @access public
		 */
		public function Route()
		{
		}
		
		/**
		 * 运行框架
		 *
		 * @access public
		 */
		public function run()
		{
			try
			{
				$this->initConfig();
				$this->initURL();
				date_default_timezone_set('Asia/Shanghai');
				$GLOBALS['time'] = time();
				$GLOBALS['date'] = date('Y-m-d',$GLOBALS['time']);
				$GLOBALS['datetime'] = date('Y-m-d H:i:s',$GLOBALS['time']);
				$this->bulid();
				$this->exec();
			}catch(Exception $e){
				$this->dealError($e);
			}
		}
		
		/**
		 * 初始化URL
		 *
		 * @access public
		 */
		private function initURL()
		{
			if(!get_magic_quotes_gpc())
			{
				$this->addS($_POST);
				$this->addS($_GET);
				$this->addS($_COOKIE);
			}
			$this->addS($_FILES);
			$GLOBALS['request'] = $GLOBALS['cookie'] = $GLOBALS['session'] = new stdclass();
			if(is_array($_GET))
			{
				foreach($_GET as $_key=>$_value){
					$GLOBALS['request']->$_key = $_GET[$_key];
				}
			}
			if(is_array($_POST))
			{
				foreach($_POST as $_key=>$_value){
					$GLOBALS['request']->$_key = $_POST[$_key];
				}
			}
			if(is_array($_COOKIE))
			{
				foreach($_COOKIE as $_key=>$_value)
				{
					$GLOBALS['cookie']->$_key = $_COOKIE[$_key];
				}
			}
			if(is_array($_SESSION))
			{
				foreach($_SESSION as $_key=>$_value)
				{
					$GLOBALS['session']->$_key = $_SESSION[$_key];
				}
			}
			if(is_array($_FILES))
			{
				foreach($_FILES as $_key=>$_value)
				{
					$GLOBALS['files']->$_key = $_FILES[$_key];
				}
			}
		}
		
		/**
		 * 建立路由路径
		 *
		 * @access private
		 */
		private function bulid()
		{
			$query = $_SERVER["QUERY_STRING"];
			if($_REQUEST['m'])
			{
				$this->config->model = $_REQUEST['m'];
			}
			if($_REQUEST['a'])
			{
				$this->config->action = $_REQUEST['a'];
			}
		}
		
		/**
		 * 执行路由找到的模型和动作
		 *
		 * @access private
		 */
		private function exec()
		{
			$controllerFile = $this->config->controllerDir.'Controller'.ucfirst($this->config->model).'.php';
			if(file_exists($controllerFile))
			{
				include_once($controllerFile);
				$className = 'Controller'.ucfirst($this->config->model);
			}else{
				$modelFile = $this->config->modelDir.'Model'.ucfirst($this->config->model).'.php';
				if(file_exists($modelFile))
				{
					include_once($modelFile);
					$className = 'Model'.ucfirst($this->config->model);
				}else{
					header("Location:{$this->config->webSite}");
					$this->throwError($this->config->model.': 指定的模型不存在!');
				}
			}
			$class = new $className();
			if(method_exists($class,$this->config->action) && $this->config->action!='exec')
			{
				$action = $this->config->action;
				$class->$action();
				if($class->template)
				{
					$templateFile = $this->config->templateDir.$this->config->model.DIRECTORY_SEPARATOR.$this->config->action.'.tpl';
					if(file_exists($templateFile))
					{
						$class->template->display($this->config->model.DIRECTORY_SEPARATOR.$this->config->action.'.tpl');
					}
				}
			}else{
				header("Location:{$this->config->webSite}");
				$this->throwError($this->config->action.': 指定的方法不存在!');
			}
		}
	}
?>