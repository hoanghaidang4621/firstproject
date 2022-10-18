<?php

/**
 * Services are globally registered in this file
 */

use Phalcon\Mvc\Router;
use Phalcon\DI\FactoryDefault;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Loader;
use Phalcon\Mvc\Model\Manager;

/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new FactoryDefault();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader = new Loader();
$loader->registerNamespaces(array(
    'testproject\Models' => __DIR__ . '/../apps/models/',
    'testproject\Repositories' => __DIR__ . '/../apps/repositories/',
    'testproject\Utils' => __DIR__ . '/../apps/library/Utils/'
));

$loader->registerDirs(
	array(
		__DIR__ . '/../apps/library/',
		__DIR__ . '/../apps/library/SMTP/'
		
	)
);
$loader->register();

/**
 * Cloud Flare Fix CUSTOMER IP
 */
function ip_in_range($ip, $range) {
    if (strpos($range, '/') !== false) {
        // $range is in IP/NETMASK format
        list($range, $netmask) = explode('/', $range, 2);
        if (strpos($netmask, '.') !== false) {
            // $netmask is a 255.255.0.0 format
            $netmask = str_replace('*', '0', $netmask);
            $netmask_dec = ip2long($netmask);
            return ( (ip2long($ip) & $netmask_dec) == (ip2long($range) & $netmask_dec) );
        }
        else {
            // $netmask is a CIDR size block
            // fix the range argument
            $x = explode('.', $range);
            while(count($x)<4) $x[] = '0';
            list($a,$b,$c,$d) = $x;
            $range = sprintf("%u.%u.%u.%u", empty($a)?'0':$a, empty($b)?'0':$b,empty($c)?'0':$c,empty($d)?'0':$d);
            $range_dec = ip2long($range);
            $ip_dec = ip2long($ip);

            # Strategy 1 - Create the netmask with 'netmask' 1s and then fill it to 32 with 0s
            #$netmask_dec = bindec(str_pad('', $netmask, '1') . str_pad('', 32-$netmask, '0'));

            # Strategy 2 - Use math to create it
            $wildcard_dec = pow(2, (32-$netmask)) - 1;
            $netmask_dec = ~ $wildcard_dec;

            return (($ip_dec & $netmask_dec) == ($range_dec & $netmask_dec));
        }
    }
    else {
        // range might be 255.255.*.* or 1.2.3.0-1.2.3.255
        if (strpos($range, '*') !==false) { // a.b.*.* format
            // Just convert to A-B format by setting * to 0 for A and 255 for B
            $lower = str_replace('*', '0', $range);
            $upper = str_replace('*', '255', $range);
            $range = "$lower-$upper";
        }

        if (strpos($range, '-')!==false) { // A-B format
            list($lower, $upper) = explode('-', $range, 2);
            $lower_dec = (float)sprintf("%u",ip2long($lower));
            $upper_dec = (float)sprintf("%u",ip2long($upper));
            $ip_dec = (float)sprintf("%u",ip2long($ip));
            return ( ($ip_dec>=$lower_dec) && ($ip_dec<=$upper_dec) );
        }
        return false;
    }
}
if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $cf_ip_ranges = array('204.93.240.0/24',
        '204.93.177.0/24',
        '199.27.128.0/21',
        '172.64.0.0/13',
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '104.16.0.0/12',
        '131.0.72.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15',
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
        '2c0f:f248::/32',
        '2a06:98c0::/29');
    foreach ($cf_ip_ranges as $range) {
        if (ip_in_range($_SERVER['REMOTE_ADDR'], $range)) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
            break;
        }
    }
}


/**
 * Registering a router
 */
$di['router'] = function () {
    $router = new Router();
	$router->removeExtraSlashes(true);
	$router->setDefaultModule("frontend");
	
	//Set 404 paths
    $router->notFound(array(
		"module" => "frontend",
		"controller" => "notfound",
		"action" => "index"
	));
	
	//Set a router not found
     $router->add("/notfound", array(
		"module" => "frontend",
        "controller" => "notfound",
        "action"     => "index"
    ));
	
    //Define a router index
    $router->add("/", array(
		"module" => "frontend",
        "controller" => "index",
        "action"     => "index"
    ));
	$router->add("/login", array(
		"module"	=> "frontend",
		"controller" => "login",
        "action"     => "index"
	));
		$router->add("/register", array(
		"module"	=> "frontend",
		"controller" => "login",
        "action"     => "register"
	));

    $router->add('/product', array(
		"module" => "frontend",
		"controller" => "product",
		"action"	=> "getProduct"
	));
    $router->add('/product/:action/:params', array(
		"module" => "frontend",
		"controller" => "product",
		"action"	=> 1,
        'params'     => 2,
	));

	$router->add('/dashboard', array(
		"module" => "backend",
		"controller" => "index",
		"action"	=> "index"
    ));
	$router->add('/dashboard/:controller', array(
		"module" => "backend",
		"controller" => 1,
		"action"	=> "index"
	));
//handle
	$router->handle();
    return $router;
};

/**
 * Start the session the first time some component request the session service
 */
$di['session'] = function () {
    $session = new SessionAdapter();
    $session->start();
    return $session;
};
/**
 * Register My component
 */
$di->set('my', function(){
    return new \My();
});

/**
 * Register GlobalVariable component
 */
$di->set('globalVariable', function(){
    return new \GlobalVariable();
});

/**
 * Register cookie
 */
$di->set('cookies', function() {
    $cookies = new \Phalcon\Http\Response\Cookies();
	$cookies->useEncryption(false);
    return $cookies;
}, true);

/**
 * Register key for cookie encryption
 */
$di->set('crypt', function() {
    $crypt = new \Phalcon\Crypt();
    $crypt->setKey('binmedia123@@##'); //Use your own key!
    return $crypt;
});

/**
 * Register models manager
 */
$di->set('modelsManager', function() {
      return new Manager();
});

/**
 * Register PHPMailer manager
 */
$di->set('myMailer', function() {
	//require_once(__DIR__ . "/../apps/library/SMTP/class.phpmailer.php");
	$mail = new \PHPMailer();
	$mail->IsSMTP();//telling the class to use SMTP
	$mail->SMTPAuth   = true;
	$mail->SMTPSecure = "tls";
	$mail->Host       = "email-smtp.us-east-1.amazonaws.com";
	$mail->Username   = "AKIAJB6GS6WBGRX5J3FQ";
	$mail->Password   = "Arqy+RpnBR5GConY7i5yx9kkMvpVJZJsHmeyHv4SZAST";
    return $mail;
});


//
$di->set(
    'view',
    function () {
        $view = new View();
        $view->setViewsDir(__DIR__ . '/views/');
        return $view;
    }
);