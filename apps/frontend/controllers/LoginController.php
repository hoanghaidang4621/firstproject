<?php

namespace testproject\Frontend\Controllers;

use ModelTest\Product;
use Phalcon\Mvc\View;
use Phalcon\Http\Response\Cookies;
use Phalcon\Tag;

use testproject\Utils\PasswordGenerator;
use testproject\Utils\Validator;

use testproject\Models\VisaUser;

use testproject\Repositories\User,
	testproject\Repositories\Country;

class LoginController extends ControllerBase
{
	//index Action
	public function indexAction()
	{
		$this->view->id_info = "login";
		$action = $this->request->get("action");
		switch($action)
		{
			case "logout":
				$this->dispatcher->forward(array(
					"module"	=>	"frontend",
					"controller" => "login",
					"action" => "logout"
				));
				break;
			case "register":
				$this->dispatcher->forward(array(
					"module"	=>	"frontend",
					"controller" => "login",
					"action" => "register"
				));
				break;
			case "forgot":
				$this->dispatcher->forward(array(
					"module"	=>	"frontend",
					"controller" => "login",
					"action" => "forgot"
				));
				break;
			default:
				$action = 'login';
				$this->dispatcher->forward(array(
					"module"	=>	"frontend",
					"controller" => "login",
					"action" => "login"
				));
				break;
		}
	}
	//generate breadcrumb
	private function generateBreadCrumb($steps)
	{
		$result = array();
		foreach($steps as $k => $v)
		{
			if($v[0]==true)//show link
			{
				$link = "login?action=".$k;
			}
			else
			{
				$link = "";
			}
			$result[] = array(
				"url" => $this->url->get($link),
				"title" => $v[2],
				"selected" => $v[1]
			);
		}
		return $result;
	}
	//forgot Action
	public function forgotAction()
	{
		$userRepo = new User();
		$countryRepo = new Country();
		if ($this->request->isPost() == true)
		{
			$email = $this->request->getPost("email");
			$user = $userRepo->findByEmail($email);
			if($user)
			{
				$passGenerator = new PasswordGenerator();
				$newPass = $passGenerator->generatePassword(10);
				$user->setUserPassword($newPass);
				$save = $userRepo->saveUser($user);
				if($save['success']==true)
				{
					$country = $countryRepo->findByID($user->getUserCountryID());//id=0 => $country != NULL
					if(!empty($country))
					{
						$countryName = $country->getCountryName();
					}
					else
					{
						$countryName = "";
					}
					$this->sendRecoverEmail($user, $country);
					$this->view->message = array(
						"type" => "success",
						"message" => "New password has been sent to your registered email!"
					);
				}
				else
				{
					$this->view->message = array(
						"type" => "error",
						"message" => $result['message']
					);
				}
			}
			else
			{
				$this->view->message = array(
					"type" => "error",
					"message" => "Email not exists in our system!"
				);
			}
		}
		$this->view->breadcrumbs = $this->generateBreadCrumb(array(
			"forgot" => array(true, true, "Recover Password")
		));
		$this->tag->setTitle('Recover Password');
		$this->view->actionTitle = 'RECOVER PASSWORD';
		$this->view->pick('login/forgot');
	}
	//log in Action
	public function loginAction()
	{
		/*?>if(!$this->auth)
		{	
			if($this->cookies->has('email') && $this->cookies->has('password'))
			{
				$email = $_COOKIE['email'];
				$password = $_COOKIE['password'];
				$this->logUserIn($email, $password);
			}
			if ($this->request->isPost() == true)
			{
				$email = $this->request->getPost("email");
				$password = $this->request->getPost("password");
				$passGenerator = new PasswordGenerator();
				$password = $passGenerator->encryptPassword($password);
				$this->logUserIn($email, $password);
			}
			if($this->auth)//if auth variable exist => user has logined
			{
				return;
			}
			//not login or request isn't post or invalid email/password or no cookie => show login form
			$this->view->breadcrumbs = $this->generateBreadCrumb(array(
				"index" => array(true, true, "Login / Register")
			));
			$countryRepo = new Country();
			$this->tag->setTitle('Login / Register');
			$this->view->actionTitle = 'LOGIN / REGISTER';
			$this->view->countries = $countryRepo->findAll();
		}
		else
		{
			$this->response->redirect('account');
		}<?php */
	}
	//log out Action
	public function logoutAction()
	{
		$destoyed = $this->session->destroy();
		setcookie('email', '', time()-3600);
		setcookie('password', '', time()-3600);
		$this->response->redirect('');
	}
	//log user in
	private function logUserIn($email, $password)
	{
		$userRepo = new User();
		$user = $userRepo->findByEmailPassword($email, $password);
		if($user)
		{
			$this->startSession($user);
		}
		else
		{
			$this->view->message = invalid_email_password_key;
		}
	}
	//register Action
	public function registerAction()
	{
		$validator = new Validator();
		$userRepo = new User();
		$countryRepo = new Country();
		if ($this->request->isPost() == true)
		{
			$name = $this->request->getPost("name");
			$email = $this->request->getPost("email");
			$password = $this->request->getPost("password");
			$confirmPassword = $this->request->getPost("confirm-password");
			$skype = $this->request->getPost("skype");
			$tel = $this->request->getPost("tel");
			$countryID = $this->request->getPost("country");
			$address = $this->request->getPost("address");
			if($validator->validInt($countryID)==false)
			{
				$countryID = 0;
			}
			$inputData = array(
				$name,
				$email,
				$password,
				$confirmPassword,
				$tel,
				$countryID,
				$address,
				$skype
			);
			if($countryID>0)
			{
				$country = $countryRepo->findByID($countryID);
			}
			if(empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL) ||
				empty($password) || $password!=$confirmPassword ||
				$validator->validTel2($tel)==false || empty($address) ||
				!isset($country)
			)
			{
				$this->view->message = "Invalid data! Please try again later!";
			}
			else
			{
				$userByEmail = $userRepo->findByEmail($email);
				if($userByEmail)
				{
					$this->view->message = "Input email is existent! Please try with another!";
				}
				else
				{
					$convience = 'email_tel';
					if(strlen($skype)>0) $convience .= "_skype";
					$passGenerator = new PasswordGenerator();
					$newUser = new VisaUser();
					$newUser->setUserName($name);
					$newUser->setUserEmail($email);
					$password = $passGenerator->encryptPassword($password);
					$newUser->setUserPassword($password);
					$newUser->setUserSkype($skype);
					$newUser->setUserTel($tel);
					$newUser->setUserCountryID($countryID);
					$newUser->setUserAddress($address);
					$newUser->setUserInsertTime(time()+8*3600);
					$newUser->setUserConvience($convience);
					$newUser->setUserActive('Y');
					$newUser->setUserRole('user');
					$newUser->setUserSiteCountryID($this->globalVariable->siteCountryID);
					$result = $userRepo->saveUser($newUser);
					if($result['success']==true)
					{
						$this->startSession($newUser);
						$this->sendRegisterEmail($newUser, $country);
					}
					else
					{
						$this->view->message = $result['message'];
					}
				}
			}
			$this->view->inputData = $inputData;
		}
		$this->view->breadcrumbs = $this->generateBreadCrumb(array(
			"index" => array(true, true, "Login / Register")
		));
		$countryRepo = new Country();
		$this->tag->setTitle('Login / Register');
		$this->view->actionTitle = 'LOGIN / REGISTER';
		$this->view->countries = $countryRepo->findAll();//not work if place after $this->view->partial('login/login');
		$this->view->partial('login/login');
	}
	//start user session
	private function startSession($user)
	{
		$countryRepo = new Country();
		$country = $countryRepo->findByID($user->getUserCountryID());//id=0 => $country != NULL
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
			'skype' => $user->getUserSkype(),
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
		$preURL = $this->session->get('preURL');
		$this->session->remove('preURL');
		if($preURL)
		{
			$this->response->redirect($preURL);
		}
		else
		{
			//no previous request URL
			$this->response->redirect('account');
		}
	}
	//send registration email
	private function sendRegisterEmail($user, $country)
	{
		$passGenerator = new PasswordGenerator();
		$fromEmail = "account@bin.vn";
		$toEmail = $user->getUserEmail();
		$subject = "Congratulation for successful registration";
		require_once __DIR__ ."/../views/login/accountmail.phtml";
		$fromFullName = "Bin";
		$toFullname = $user->getUserName();
		$replyToEmail = "account@bin.vn";
		$replyToName = $fromFullName;
		//send user
		$this->my->sendEmail($fromEmail, $toEmail, $subject, $message, $fromFullName, $toFullname, $replyToEmail, $replyToName);
		//send admin
		$fromEmail = "noreply@bin.vn";
		$toEmail = "account@bin.vn";
		require_once __DIR__ ."/../views/login/adminaccountmail.phtml";
		$subject = "#".$fUserID." Congratulation for successful registration";
		$fromFullName = $user->getUserName();
		$toFullname = "Bin";
		$replyToEmail = $user->getUserEmail();
		$replyToName = $user->getUserName();
		$this->my->sendEmail($fromEmail, $toEmail, $subject, $adminMessage, $fromFullName, $toFullname, $replyToEmail, $replyToName);
	}
	//send recover email
	private function sendRecoverEmail($user, $country)
	{
		$passGenerator = new PasswordGenerator();
		$fromEmail = "account@bin.vn";
		$toEmail = $user->getUserEmail();
		$subject = "Notification of password recovery";
		require __DIR__ ."/../views/login/accountmail.phtml";
		$fromFullName = "Bin";
		$toFullname = $user->getUserName();
		$replyToEmail = "account@bin.vn";
		$replyToName = $fromFullName;
		$result = $this->my->sendEmail($fromEmail, $toEmail, $subject, $message, $fromFullName, $toFullname, $replyToEmail, $replyToName);
		return $result;
	}
}
