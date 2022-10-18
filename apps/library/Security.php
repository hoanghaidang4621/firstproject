<?php
use Phalcon\Events\Event,
	Phalcon\Mvc\User\Plugin,
	Phalcon\Mvc\Dispatcher,
	Phalcon\Acl;
/**
 * Security
 * This is the security plugin which controls that users only have access to the modules they're assigned to
 */
class Security extends Plugin
{
	private $frontend;
	private $backend;
	public function __construct($dependencyInjector)
	{
		$this->_dependencyInjector = $dependencyInjector;
	}
	//get Acl
	public function getAcl()
	{
		$acl;
		if (!isset($this->persistent->acl)) {
			$acl = new Phalcon\Acl\Adapter\Memory();
			$acl->setDefaultAction(Acl::DENY);
			/*Register roles*/
			$guest = new \Phalcon\Acl\Role('guest');
			$acl->addRole($guest);
			$user = new \Phalcon\Acl\Role('user');
			$acl->addRole($user, 'guest');
			$editor = new \Phalcon\Acl\Role('editor');
			$acl->addRole($editor, 'user');
			
			/*Guest resources*/
			$publicResources = array(
				'frontendindex' => array('index'),
				'frontendlogin' => array('index', 'login', 'logout', 'forgot', 'register'),
				'frontendnotfound' => array('index'),
				'frontendproduct'=> array('index','getProduct','editproduct'),
				'backendlogin' => array('index', 'login', 'logout')
			);
			foreach ($publicResources as $resource => $actions) {//$resource is key, $actions is value
				$acl->addResource(new \Phalcon\Acl\Resource($resource), $actions);
			}
			
			/*User resources*/
			$userResources = array(
			);
			foreach ($userResources as $resource => $actions) {
				$acl->addResource(new \Phalcon\Acl\Resource($resource), $actions);
			}
			
			/*Editor resources*/
			$editorResources = array(
				'backendindex' => array('index')
			);
			foreach ($editorResources as $resource => $actions) {
				$acl->addResource(new \Phalcon\Acl\Resource($resource), $actions);
			}
			
			/*Grant access to public areas to guest, user and editor*/
			foreach ($publicResources as $resource => $actions) {
				$acl->allow('guest', $resource, $actions);
			}
			foreach ($userResources as $resource => $actions) {
				$acl->allow('user', $resource, $actions);
			}
			$acl->deny("user", "frontendlogin", array('register', 'forgot'));
			
			/*Grant access to editor area to role editor*/
			foreach ($editorResources as $resource => $actions) {
				$acl->allow('editor', $resource, $actions);
			}
			
			// Store serialized ACL
			$this->persistent->acl = $acl;
		}
		else
		{
			 //Restore ACL object from serialized ACL
			 $acl = $this->persistent->acl;
		}
		return $acl;
	}
	/**
	 * This action is executed before execute any action in the application
	 * @param Event $event
	 * @param Dispatcher $dispatcher
	 */
	public function beforeDispatch(Event $event, Dispatcher $dispatcher)
	{
		$auth = $this->session->get('auth');
		if(!$auth){
			$role = 'guest';
		}
		else
		{
			$role = $auth['role'];
		}
		$module = $dispatcher->getModuleName();
		$controller = $dispatcher->getControllerName();
		$action = $dispatcher->getActionName();
		$acl = $this->getAcl();
		$resource = $module.$controller;
		$allowed = $acl->isAllowed($role, $module.$controller, $action);
		if ($allowed != Acl::ALLOW)
		{
			if($acl->isResource($resource))
			{
				switch($auth['role'])
				{
					case "user":
						$this->response->redirect('account');
						return;
				}
				$preURl = str_ireplace($this->url->getBaseUri(), '', $this->request->getURI());
				$this->session->set('preURL', $preURl);
				switch($module)
				{
					case "backend":
						$this->response->redirect('dashboard/login');
						break;
					default:
						$this->response->redirect('login');
						break;
				}
			}
			else{
				//////////////////Add 27 - 11///////////////////////
				//$message = "URL Notfound : ".$message .= $_SERVER['REQUEST_URI']."<br>";
				$message .= "=================================== USER AGENT ======================================================<br>";
				//$message .= $_SERVER['HTTP_USER_AGENT'];
				// All Info User
				$indicesServer = array('PHP_SELF', 
				'argv', 
				'argc', 
				'GATEWAY_INTERFACE', 
				'SERVER_ADDR', 
				'SERVER_NAME', 
				'SERVER_SOFTWARE', 
				'SERVER_PROTOCOL', 
				'REQUEST_METHOD', 
				'REQUEST_TIME', 
				'REQUEST_TIME_FLOAT', 
				'QUERY_STRING', 
				'DOCUMENT_ROOT', 
				'HTTP_ACCEPT', 
				'HTTP_ACCEPT_CHARSET', 
				'HTTP_ACCEPT_ENCODING', 
				'HTTP_ACCEPT_LANGUAGE', 
				'HTTP_CONNECTION', 
				'HTTP_HOST', 
				'HTTP_REFERER', 
				'HTTP_USER_AGENT', 
				'HTTPS', 
				'REMOTE_ADDR', 
				'REMOTE_HOST', 
				'REMOTE_PORT', 
				'REMOTE_USER', 
				'REDIRECT_REMOTE_USER', 
				'SCRIPT_FILENAME', 
				'SERVER_ADMIN', 
				'SERVER_PORT', 
				'SERVER_SIGNATURE', 
				'PATH_TRANSLATED', 
				'SCRIPT_NAME', 
				'REQUEST_URI', 
				'PHP_AUTH_DIGEST', 
				'PHP_AUTH_USER', 
				'PHP_AUTH_PW', 
				'AUTH_TYPE', 
				'PATH_INFO', 
				'ORIG_PATH_INFO') ; 
			
				echo '<table cellpadding="10">' ; 
				foreach ($indicesServer as $arg) { 
					if (isset($_SERVER[$arg])) { 
						$message .= '<tr><td>'.$arg.'</td><td>' . $_SERVER[$arg] . '</td></tr>' ; 
					} 
					else { 
						 $message .= '<tr><td>'.$arg.'</td><td>-</td></tr>' ; 
					} 
				} 
				echo '</table>' ; 
				$title = "URL NOTFOUND";
				//$result = $this->my->sendError($message,$title);
				//////////////Add 27 - 11//////////////
				$this->response->redirect('notfound/index');//404 not found
			}
			return false;
		}
	}
}

