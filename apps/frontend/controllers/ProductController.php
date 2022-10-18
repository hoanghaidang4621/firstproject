<?php

namespace testproject\Frontend\Controllers;

use ModelTest\Product;
use Phalcon\Mvc\View;
use Phalcon\Http\Response\Cookies;
use Phalcon\Tag;
use testproject\Models\Product as ModelsProduct;
use testproject\Utils\PasswordGenerator;
use testproject\Utils\Validator;

use testproject\Models\VisaUser;

use testproject\Repositories\User,
	testproject\Repositories\Country;

class ProductController extends ControllerBase
{


	public function getProductAction()
	{
        $products = ModelsProduct::find();
        $this->view->products = $products;
	}
    public function editproductAction($product_id)
	{
       echo($product_id);
	}
}
