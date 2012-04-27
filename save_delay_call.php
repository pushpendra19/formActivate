<?php

error_reporting(E_ALL);

ini_set('display_errors',1);

$encodedData = $_REQUEST['data'];

//include('twilios/config.php');

$nextTime = time() + 5 * 60;

$SQL = "INSERT INTO
            delay_calls(user_data,  next_call_time, status)
        VALUES('{$encodedData}', '{$nextTime}', 1);";
        
mysql_query($SQL) or die($SQL);

?>