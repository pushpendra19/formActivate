<?php
/*

This is cron file which will update user call plan after 7 day trial period in the data base 

 * @filesource
 * @package			formsbuilder
 * @subpackage		Index.controller
 * @createdby		Saurabh Agarwal
 * @created			$Date: 2011-05-11 
 * @modifiedby		Saurabh Agarwal
 * @lastmodified	$Date: 2011-05-11

*/

ini_set('display_error',1);
error_reporting(E_ALL);
// connect with database    
// get call SID for the twilio calls
//include('config.php');
$query="update members set plan_id=2 WHERE DATEDIFF(CURDATE(), plan_start_date)>=7 and plan_id=5 limit 0,100";
$res=mysql_query($query);
//echo mysql_affected_rows($res);
 exit;   
	
?>
