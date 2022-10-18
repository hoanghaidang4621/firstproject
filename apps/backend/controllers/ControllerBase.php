<?php

namespace testproject\Backend\Controllers;

class ControllerBase extends \Phalcon\Mvc\Controller
{
	protected $lang;
	protected $auth;
	public function initialize()
    {
		//current user
		$this->auth = $this->session->get('auth');
		//set language
		$this->lang = $this->session->get('lang');
		if(!$this->lang)
		{
			$this->lang = $this->dispatcher->getParam("language");
			if($this->lang!=="en"&&$this->lang!=="ch")
			{
				$this->lang="en";
			}
		}
		$this->my->setLanguage($this->lang);
		$this->auth = $this->session->get('auth');
    }
}
