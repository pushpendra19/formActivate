<?php
require_once('constant.php');
require_once(WEBROOT_PATH.'braintree-php-2.3.0/lib/Braintree.php');
Braintree_Configuration::environment('sandbox');
Braintree_Configuration::merchantId('jf5qp9jc6wsb2gs7');
Braintree_Configuration::publicKey('9pdnyn263m8p3hg5');
Braintree_Configuration::privateKey('7dysmj56j7fs88bf');
?>
