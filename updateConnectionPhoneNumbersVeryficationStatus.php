<?php
require 'constant.php';
error_reporting(E_ALL);
ini_set('display_errors',1);

$form_id = trim($_GET['form_id']);
$number_type = trim($_GET['number_type']);
$col_name= trim($_GET['phone_type']);
$customet_id = trim($_GET['customerId']);

$verificationStatus="failed";
if(isset($_POST['VerificationStatus']))
{
	$verificationStatus = $_POST['VerificationStatus'];
	$bitChanged = 0;
	if(trim($verificationStatus) == 'success')
		$bitChanged = 1;
	$time = time();
	
	$update_forms_table="update forms set $col_name = '$bitChanged' where id='".$form_id."' and customer_id='".$customet_id."'";
	
	
	$con = mysql_connect(DB_HOST,DB_USERNAME,DB_USER_PASSWORD);
	if (!$con)
	{
	 	 die('Could not connect: ' . mysql_error());
	}
	mysql_select_db(DB_DBNAME,$con);
	$res = mysql_query($update_forms_table);
	mysql_close($con);
}
	
?>