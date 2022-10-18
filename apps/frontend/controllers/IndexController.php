<?php
namespace testproject\Frontend\Controllers;

class IndexController extends ControllerBase
{
    public function indexAction()
    {
		$this->tag->setTitle("Test");
    }
}

