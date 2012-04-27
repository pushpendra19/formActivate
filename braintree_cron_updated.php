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
require_once('constant.php');
require_once('braintree-php-2.3.0/lib/Braintree.php');
Braintree_Configuration::environment(ENVIRONMENT);
Braintree_Configuration::merchantId(MERCHANTID);
Braintree_Configuration::publicKey(PUBLICKEY);
Braintree_Configuration::privateKey(PRIVATEKEY);
echo "Cron page for recurring payment.";
$query="SELECT * FROM `members` WHERE `recurring_end_date`='".date('Y-m-d')."' and status='Active' and payment_due_for_coming_month=1";
$recurring_end_date=date('Y-m-d',strtotime(date("Y-m-d", strtotime(date("Y-m-d"))) . " +1 month"));							

$res=mysql_query($query);
$cnt=0;
$cnt=mysql_num_rows($res);
if($cnt>0){
while($row=mysql_fetch_array($res))
{
	$id=$row['id'];
	$braintree_customer_id=$row['braintree_customer_id'];
	$braintree_token=$row['braintree_token'];
	$subscription_price=mysql_fetch_array(mysql_query("SELECT * FROM `subscriptions` WHERE `id`='".$row['plan_id']."'"));
	$plan_id=$subscription_price['id'];
	
	/**
	 * PLAN_OVERIDE CHANGE @ PUSHPENDRA
	 */
		$price_to_be_charge = 0;
		if($row['override_price'] != null && $row['override_price'] != 0 && $row['override_price'] != '')
			$price_to_be_charge = $row['override_price'];
		else
			$price_to_be_charge = $subscription_price['price'];
	
		
	/**
	 * PLAN_OVERIDE CHANGE @ PUSHPENDRA
	 */
		
	$result = Braintree_Transaction::sale(array(
									  'amount' => $price_to_be_charge,
									  'customerId' => $row['braintree_customer_id'],
									  'paymentMethodToken' => $row['braintree_token']
									));		
	if( $result->success==1)
	{
		$payment_status ='Confirmed';
		$order_status= 'Confirmed';		
	}else
	{
		$payment_status ='Fail';
		$order_status='Fail';		
	}

	
	mysql_query("INSERT INTO `orders` (`plan_id` , `member_id` , `created_on` , `order_status` , `card_type` , `name_on_card` ,
	 `card_number` , `cvv_number` , `expiry_month` , `expiry_year` , `amount` , 
	 `transaction_id` , `user_status`,payment_type ) 
VALUES ('".$plan_id."','".$id."','".date('Y-m-d,H:i:s')."' , '".$order_status."' ,'".$row['card_type']."', '".$row['card_name']."' , 
'".mysql_real_escape_string(addslashes($row['card_num']))."','".$row['card_cvv']."','".$row['expiry_month']."','".$row['expiry_year']."',
'".$price_to_be_charge."','".$result->transaction->id."','".$row['status']."','Cron')");
	$order_id=mysql_insert_id();			
	
	$updatequery=("UPDATE `members` SET `created` ='".date('Y-m-d,H:i:s')."' ,recurring_start_date='".date('Y-m-d')."',
	recurring_end_date='".$recurring_end_date."',payment_status='".$payment_status."',`order_id` = '".$order_id."' WHERE `id` ='".$id."' LIMIT 1");
	$res1=mysql_query($updatequery);
	
		$Body="<div style='color:black;'>Dear ".ucfirst($row ['firstname']).",<br><br>";
		$Body.="Your account is recharged by the system. Your new subscription plan entitles you to ".$subscription_price['name']." at a rate of $".$price_to_be_charge;
		$Body.="Remember, you can upgrade and downgrade your account as frequently as you would like.<br><br>";	
		$Body.="Thank you, <br><span style='color:black;'> ".WEBSITE_NAME." Team</span></div>";										
		$subject = 'Your subscription charged on '.WEBSITE_NAME;
		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";		
		// Additional headers
		$headers .= 'To: '.$row ['firstname'].' <'.$row ['email'].'>'. "\r\n";
		$headers .= 'From: Admin <'.$row ['email'].'>' . "\r\n";
		mail($row ['email'], $subject, $Body, $headers);
}	
}
?>
