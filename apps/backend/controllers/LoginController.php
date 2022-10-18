<?php
namespace testproject\Backend\Controllers;

use Phalcon\Mvc\View;
use Phalcon\Http\Response\Cookies;

use testproject\Utils\PasswordGenerator;

use testproject\Models\VisaUser;

use testproject\Repositories\User,
	testproject\Repositories\Country;

class LoginController extends ControllerBase
{
	public function indexAction()
	{
		$action = $this->request->get("action");
		switch($action)
		{
			case "logout":
				$this->dispatcher->forward(array(
					"module"	=>	"backend",
					"controller" => "login",
					"action" => "logout"
				));
				break;
			default:
				$this->dispatcher->forward(array(
					"module"	=>	"backend",
					"controller" => "login",
					"action" => "login"
				));
				break;
		}
	}
	//login action
	public function loginAction()
	{
		if(!$this->auth)
		{
			if($this->cookies->has('email') && $this->cookies->has('password'))
			{
				$email = $_COOKIE['email'];
				$password = $_COOKIE['password'];
				$language = $_COOKIE['language'];
				//$email = $this->cookies->get('email');
				//$password = $this->cookies->get('password');
				//$this->view->message = "Cookie invalid_email_password ".$remember;
				$this->logUserIn($email, $password, $language, false);
			}
			if ($this->request->isPost() == true)
			{
				$email = $this->request->getPost("email");
				$password = $this->request->getPost("password");
				$language = $this->request->getPost("language");
				$remember_me = $this->request->getPost("remember-me");
				//$this->view->message = "Language ".$language;
				$passGenerator = new PasswordGenerator();
				$password = $passGenerator->encryptPassword($password);
				$this->logUserIn($email, $password, $language, $remember_me);
			}
			if($this->auth)//if auth variable exist => user has logined
			{
				return;
			}
			//not login or request isn't post or invalid email/password or no cookie => show login form
			$this->view->disableLevel(array(
				View::LEVEL_LAYOUT => false,
				View::LEVEL_MAIN_LAYOUT => false
			));
			$this->tag->setTitle('Login');
			$this->view->pick('login/index');
		}
		else
		{
			$this->response->redirect('dashboard');
		}
	}
	//logout action
	public function logoutAction()
	{
		$destoyed = $this->session->destroy();
		setcookie('email', '', time()-3600);
		setcookie('password', '', time()-3600);
		$this->response->redirect('dashboard/login');
	}
	//log user in
	private function logUserIn($email, $password, $language, $remember_me=false)
	{
		$this->my->setLanguage($language);
		$userRepo = new User();
		$user = $userRepo->findByEmailPasswordRole($email, $password, "editor");
		if($user)
		{
			$countryRepo = new Country();
			$country = $countryRepo->findByID($user->getUserCountryID());
			if(!empty($country))
			{
				$countryName = $country->getCountryName();
			}
			else
			{
				$countryName = "";
			}
			$this->session->set('auth', array(
				'id' => $user->getUserId(),
				'name' => $user->getUserName(),
				'email' => $user->getUserEmail(),
				'tel' => $user->getUserTel(),
				'countryID' => $user->getUserCountryID(),
				'countryName' => $countryName,
				'address' => $user->getUserAddress(),
				'convience' => $user->getUserConvience(),
				'avatar' => $user->getUserAvatar(),
				'role' => $user->getUserRole(),
				'insertTime' => $user->getUserInsertTime()
			));
			$this->auth = $this->session->get('auth');//update auth variable in ControllerBase
			if($remember_me)
			{
				//$this->cookies->set('email', $email, time() + 15 * 86400);
				//$this->cookies->set('$password', $password, time() + 15 * 86400);
				setcookie('email', $email, time() + 15 * 86400);
				setcookie('password', $password, time() + 15 * 86400);
				setcookie('language', $language, time() + 15 * 86400);
			}
			$preURL = $this->session->get('preURL');
			$this->session->remove('preURL');
			if($preURL)
			{
				$this->response->redirect($preURL);
			}
			else
			{
				//no previous request URL
				$this->response->redirect('dashboard');
			}
		}
		else
		{
			$this->view->currentLanguage = $language;
			$this->view->message = invalid_email_password_key;
		}
	}
}