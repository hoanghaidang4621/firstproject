<?php

namespace testproject\Frontend\Controllers;
use Phalcon\Mvc\Controller;

class ControllerBase extends Controller
{
	protected $lang;
	protected $auth;
	public function initialize()
    {
		//current user
		$this->auth = $this->session->get('auth');
    }
}
