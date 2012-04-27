<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

/*

This is cron file which will insert twilio call duration into formsbuilder database
 * @filesource
 * @package			formsbuilder
 * @subpackage		Index.controller
 * @createdby		Saurabh Agarwal
 * @created			$Date: 2011-05-06 
 * @modifiedby		Saurabh Agarwal
 * @lastmodified	$Date: 2011-05-06

*/

// get call SID for the twilio calls
//include('config.php');
require 'constant.php';
$con = mysql_connect(DB_HOST,DB_USERNAME,DB_USER_PASSWORD);
	if (!$con)
	{
	 	 die('Could not connect: ' . mysql_error());
	}
	mysql_select_db(DB_DBNAME,$con);
	
	
include('twilio_get_sid_cron_updated.php');

require "twilio.php";
ini_set('display_error',1);
error_reporting(E_ALL);

//$AuthToken = "59a5afb98c483cd726c7cf0943315e36";
$AccountSid = "AC2dbe8a176e89ff7f6641d4d03c047bca";
$AuthToken  = "1a0e256d656ebef49cbe84cd5e441501";

// Outgoing Caller ID you have previously validated with Twilio
// $CallerID = '858-401-2688';
//$CallerID = '917-338-7987';

$CallerID = $_POST['From'];

try
{

$client = new TwilioRestClient($AccountSid, $AuthToken);

$twilio_sid = $_POST['CallSid'];

$start_time = '';
$end_time = '';
$status = $_POST['CallStatus'];
$duration = $_POST['CallDuration'];

$update_qry="update inquiry set call_duration ='".$duration."',connection_duration ='".$duration."',call_start_time='".$start_time."',call_end_time='".$end_time."',inquiry_type='".$status. "', statucallbackdata ='".print_r($_REQUEST,true)."'  where twilio_sid='".$twilio_sid."'";
		//echo $update_qry;

$res2=mysql_query($update_qry);


$sec = $duration;
$min = $sec/60;
$min = ceil ( $min );
$customer_id = trim($_GET['customer_id']);



	$update_inquiry_qry="update customers set total_remaining_calls = total_remaining_calls - '".$min."' where id='".$customer_id."'";
		//echo $update_qry;

$res2=mysql_query($update_inquiry_qry);
//exit;


				

$query="SELECT id,twilio_sid FROM inquiry WHERE twilio_sid != '' and ((call_start_time='' and call_end_time='') or (call_start_time IS NULL and call_end_time IS NULL)) limit 0,100";
$res=mysql_query($query);
$cnt=0;
$cnt=mysql_num_rows($res);
if($cnt>0){
    while($row=mysql_fetch_array($res))
    {
            $id=0;
            $response=$twilio_sid='';
            $id=$row['id'];
            $twilio_sid=$row['twilio_sid'];
            $response = $client->request("/2010-04-01/Accounts/$AccountSid/Calls/$twilio_sid");
            $res1='';
            $res1 = new SimpleXMLElement($response->ResponseText);
            foreach ($res1->children() as $child)
            {
                     $sid=$child->Sid;
                     $start_time=$child->StartTime;
                     $end_time=$child->EndTime;
                     $duration=$child->Duration;
                     $status=$child->Status;
                     //echo "<br/>====================================<br/>";
                    $update_qry="update inquiry set call_duration ='".$duration."',connection_duration ='".$duration."',call_start_time='".$start_time."',call_end_time='".$end_time."',inquiry_type='".$status."', statucallbackdata ='".print_r($_POST,true)."'  where id='".$id."'";
                    //echo $update_qry;
                    $res2=mysql_query($update_qry);

            }	
    }

    $mailSQL = "SELECT * FROM inquiry WHERE twilio_sid='".$twilio_sid."'";

    $mailResult = mysql_query($mailSQL);

    if(mysql_num_rows($mailResult) > 0)
    {
        $mail_call_duration = mysql_result($mailResult, 0, 'call_duration');
        $date_created = mysql_result($mailResult, 0, 'date_created');
        $date = date('m/d/Y',strtotime($date_created));
        $call_time = mysql_result($mailResult, 0, 'time');
        $first_name = mysql_result($mailResult, 0, 'firstname');
        $last_name = mysql_result($mailResult, 0, 'lastname');
        $phone_number = mysql_result($mailResult, 0, 'customer_phone');
        if(empty($phone_number))
        	$phone_number = mysql_result($mailResult, 0, 'caller_id');
        
        if($mail_call_duration>60)
        {
                $mail_call_duration=$mail_call_duration%60;
                if($mail_call_duration<10)
                {
                        $mail_call_duration='0'.$call_duration;
                }

                $time=floor($mail_call_duration/60);
                $time = $time.":".$mail_call_duration;
        }
        else{
                $mail_call_duration=$mail_call_duration;
                if($mail_call_duration<10)
                {
                        $mail_call_duration='0'.$mail_call_duration;
                }
                $time = "0:".$mail_call_duration;
        }

        if($time == '0:00')
            $answer = "(No-answer)";
        else
            $answer = "";
        
		           $fName = ""; 
		         if(!empty($first_name))
		         	$fName = "<br /><br /> First Name : {$first_name} "; 
		
		         $lName = ""; 
		         if(!empty($last_name))
		         	$lName = "<br /><br /> Last Name : {$last_name} ";   	
        $body = "<div style='border: 1px solid #DDDDDD;min-height: 250px;overflow: hidden;padding: 15px;'>
    <img src='". WEBSITE_IMG_URL. "branding.png' /> <hr />";

		        $body.="Hi, <br /><br /> You have got a new call. <br /><br /> <b>Call Summary:</b> $fName $lName <br /><br /> CallerID : {$phone_number} <br /><br /> Date : {$date} <br /><br /> Time: {$call_time} <br /><br /> Duration: {$time} mins {$answer}<br /><br />";

        $body.=" Thanks <br><span style='color:black;'> ".WEBSITE_NAME."</span></div></div>";
        
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";        
        $headers .= 'From: '.WEBSITE_NAME.' <'.SITE_SUPPORT_EMAIL.'>' . "\r\n";
        $subject = "Call Summary";

/*
*****************************************************************************************************
 TODO: We need to use the exact functionality in ApiController.php to send out mail - which means using Zend, ensuring that the mail is sent as HTML, and embedding the Powered By FormActivate logo at the bottom
*****************************************************************************************************
        //mail($_REQUEST['notification_email'], $subject, $body, $headers);
*/

    }    

    //mail($_REQUEST['notification_email'], 'sdfasdfasd', 'adfasdfasd');
}   
	mysql_close($con);
  exit;   
}
	
catch (Exception $e)
{
	mysql_close($con);
}
?>
