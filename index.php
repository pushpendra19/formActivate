<?php
require_once('constant.php');
error_reporting(E_ALL|E_STRICT);
ini_set('display_startup_errors', 'off');
ini_set('display_errors', 'off');

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__). ''));


// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path

set_include_path(implode(PATH_SEPARATOR, array(realpath(APPLICATION_PATH . '/library'),get_include_path(),)));

$includePaths = array(
                                APPLICATION_PATH . '/library',
                                APPLICATION_PATH . '/modules/default/controllers',
                                APPLICATION_PATH . '/modules/default/models',
                                get_include_path());

set_include_path(implode(PATH_SEPARATOR, $includePaths));





/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/modules/configs/application.ini'
);
//echo'<pre>'.print_r($application);exit;
require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance()->suppressNotFoundWarnings(false);

$loader->setFallbackAutoloader(true);
$loader->suppressNotFoundWarnings(false); 


// require_once 'Zend/Db/Adapter/Pdo/Mysql.php';
require_once 'Zend/Db.php';


// Automatically load class Zend_Db_Adapter_Pdo_Mysql
// and create an instance of it.
$registry=Zend_Registry::getInstance();

$params = array(
	    'host'     => DB_HOST,
	    'username' => DB_USERNAME,
	    'password' => DB_USER_PASSWORD,
	    'dbname'   => DB_DBNAME
	);

try {
    $db = Zend_Db::factory('Pdo_Mysql', $params);
    $db->getConnection();
} catch (Zend_Db_Adapter_Exception $e) {
    echo 'perhaps a failed login credential, or perhaps the RDBMS is not running';
} catch (Zend_Exception $e) {
    echo 'perhaps factory() failed to load the specified Adapter class';
}




/* set above db instance as default adapter, can also be changes at run time */
Zend_Db_Table::setDefaultAdapter($db);
$registry->set('db',$db);


$FrontController = Zend_Controller_Front::getInstance();

$Router = $FrontController->getRouter();
$Router->addRoute("demo",
        new Zend_Controller_Router_Route
                (
                "demo",
                    array(
                        "controller" => "index",
                        "action" => "demo")
                    )
        );

$Router->addRoute("demo-confirm",
        new Zend_Controller_Router_Route
                (
                "demo-confirm",
                    array(
                        "controller" => "index",
                        "action" => "demo-confirm")
                    )
        );


$Router->addRoute("how-it-works",
        new Zend_Controller_Router_Route
                (
                "how-it-works",
                    array(
                        "controller" => "index",
                        "action" => "how-it-works")
                    )
        );


$Router->addRoute("pricing",
        new Zend_Controller_Router_Route
                (
                "pricing",
                    array(
                        "controller" => "index",
                        "action" => "pricing")
                    )
        );


$Router->addRoute("support",
        new Zend_Controller_Router_Route
                (
                "support",
                    array(
                        "controller" => "index",
                        "action" => "support")
                    )
        );


$Router->addRoute("contact",
        new Zend_Controller_Router_Route
                (
                "contact",
                    array(
                        "controller" => "index",
                        "action" => "contacts")
                    )
        ); 
$restRoute = new Zend_Rest_Route($FrontController,array(),array('default'=>array('api')));
$FrontController->getRouter()->addRoute('rest', $restRoute);

$smtpServerName = SMTP_HOST; 
$mailConf = array(
            'username' => SMTP_USERNAME,
            'password' => SMTP_USER_PASSWORD,
            'port' => SMTP_PORT,
            'auth' => 'plain' 
         );
         
$transport = new Zend_Mail_Transport_Smtp($smtpServerName, $mailConf);
Zend_Mail::setDefaultTransport($transport);

$application->bootstrap()->run();


