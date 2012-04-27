<?php

//error_reporting(E_ALL);

$forward_number = trim($_REQUEST['Digits']);

require 'constant.php';

require "twilio.php";

if(strlen($forward_number) != 10) die();

$ApiVersion = TWILIO_API_VERSION; // config variable
$AccountSid = TWILIO_ACCOUNT_SID; // take from config file
$AuthToken  = TWILIO_AUTH_TOKEN;


$data = base64_decode($_REQUEST['data']);
$data_array = array();
$data_array = explode('@#@#@@#', $data);
$user_data  = $data_array[0];
$user_phone = $data_array[1];

$client   = new TwilioRestClient($AccountSid, $AuthToken);
$CallerID = $user_phone;

$response = $client->request("/$ApiVersion/Accounts/$AccountSid/Calls",
            "POST", array(
        "From" => $CallerID,
        "To"   => $forward_number,
        "Url"  => WEBSITE_URL . "hello.php?data=" . base64_encode($data)
));