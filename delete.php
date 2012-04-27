<?php
/*

This is cron file which will do recurring payment into formsbuilder database
 * @filesource
 * @package			formsbuilder
 * @subpackage		Index.controller
 * @createdby		Saurabh Agarwal
 * @created			$Date: 2011-05-06 
 * @modifiedby		Saurabh Agarwal
 * @lastmodified	$Date: 2011-05-06
*/
error_reporting(E_ALL);
ini_set('display_errors',1);
require_once('../constant.php');
//require_once('config.php');

$query="delete FROM `members` WHERE id>2";							
$res=mysql_query($query);
$query="delete FROM `orders` WHERE id>0";							
$res=mysql_query($query);
?>
