<?php

error_reporting(E_ALL);

$link = mysql_connect(DB_HOST, DB_USERNAME, DB_USER_PASSWORD);
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
$db = mysql_select_db(DB_DBNAME, $link) or die ('could not connect with db '.DB_DBNAME); //

define("TWILIO_API_VERSION","2010-04-01");
define("TWILIO_ACCOUNT_SID","AC2dbe8a176e89ff7f6641d4d03c047bca");
define("TWILIO_AUTH_TOKEN","1a0e256d656ebef49cbe84cd5e441501");

require WEBROOT_PATH."twilio.php";

$currentTime = time();

$query="SELECT * FROM delay_calls WHERE ({$currentTime} - next_call_time) >= 300 AND status = 1";

$result = mysql_query($query) or die($query);

if(mysql_num_rows($result) > 0)
{
    while ($row = mysql_fetch_object($result)) {

        $ApiVersion = TWILIO_API_VERSION; // config variable
        $AccountSid = TWILIO_ACCOUNT_SID; // take from config file
        $AuthToken  = TWILIO_AUTH_TOKEN;

        $data = base64_decode($row->user_data);
        $data_array = array();
        $data_array = explode('@#@#@@#', $data);
        $user_data  = $data_array[0];
        $user_phone = $data_array[1];
        $to         = $data_array[2];

        $client   = new TwilioRestClient($AccountSid, $AuthToken);
        $CallerID = $user_phone;

        $response = $client->request("/$ApiVersion/Accounts/$AccountSid/Calls",
                    "POST", array(
                "From" => $CallerID,
                "To"   => $to,
                "Url"  => WEBSITE_URL . "hello.php?data=" . base64_encode($data)
        ));

        $sql = "UPDATE delay_calls SET status = 0 WHERE id = {$row->id}";

        mysql_query($sql);

//        echo '<pre>';
//        print_r($response);

    }
}



?>
