<?php
/*

This is cron file which will insert twilio call sids into formsbuilder database
 * @filesource
 * @package			formsbuilder
 * @subpackage		Index.controller
 * @createdby		Saurabh Agarwal
 * @created			$Date: 2011-05-06 
 * @modifiedby		Saurabh Agarwal
 * @lastmodified	$Date: 2011-05-06
 
 <?xml version="1.0"?>
<TwilioResponse><Call><Sid>CAbf31955af99f5ea2be9f2a7bcd0060a3</Sid><DateCreated>Mon, 16 May 2011 07:17:59 +0000</DateCreated><DateUpdated>Mon, 16 May 2011 07:17:59 +0000</DateUpdated><ParentCallSid/><AccountSid>AC2dbe8a176e89ff7f6641d4d03c047bca</AccountSid><To>+18584012688</To><From>+18584012688</From><PhoneNumberSid>PN7ea0c7f3ee60ccf59e20224e22f8a7e2</PhoneNumberSid><Status>queued</Status><StartTime/><EndTime/><Duration/><Price/><Direction>outbound-api</Direction><AnsweredBy/><ApiVersion>2010-04-01</ApiVersion><Annotation/><ForwardedFrom/><GroupSid/><CallerName/><Uri>/2010-04-01/Accounts/AC2dbe8a176e89ff7f6641d4d03c047bca/Calls/CAbf31955af99f5ea2be9f2a7bcd0060a3</Uri><SubresourceUris><Notifications>/2010-04-01/Accounts/AC2dbe8a176e89ff7f6641d4d03c047bca/Calls/CAbf31955af99f5ea2be9f2a7bcd0060a3/Notifications</Notifications><Recordings>/2010-04-01/Accounts/AC2dbe8a176e89ff7f6641d4d03c047bca/Calls/CAbf31955af99f5ea2be9f2a7bcd0060a3/Recordings</Recordings></SubresourceUris></Call></TwilioResponse>


*/
error_reporting(E_ALL);
ini_set('display_errors',1);

$query="SELECT id,response_error FROM inquiry WHERE (twilio_sid = '' OR twilio_sid is NULL) AND response_error != '' AND response_error LIKE '<?xml version=\"1.0\"?>%' limit 0,500";
$res=mysql_query($query);
$cnt=0;
$cnt=mysql_num_rows($res);
if($cnt>0){
while($row=mysql_fetch_array($res))
{

	$id=0;
	$response_error=$response=$sid='';
	$id=$row['id'];
	$response_error=$row['response_error'];
	$xml_content=stripslashes($response_error);
	$xml = new SimpleXMLElement($xml_content);

	$sid=$xml->children()->children()->Sid;

	//	$data=explode('[Sid] => ',$response_error);
	//	$response=$data[1];
	//	$new_data=explode('[DateCreated] =>',$response);
	//	$sid=trim($new_data[0]);
	//echo "Call Sid:".$sid;
	$update_qry="update inquiry set twilio_sid='".$sid."' where id='".$id."'";
	//echo $update_qry;
	$res1=mysql_query($update_qry);
}
}
//exit;



?>