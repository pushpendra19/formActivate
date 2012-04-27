<?php

define("WEBSITE_URL","http://www.formactivate.com/");

if(!empty($_REQUEST['data']))
{
	$data=base64_decode($_REQUEST['data']);
	$data_array=array();
	$data_array=explode('@#@#@@#',$data);
	$user_data=$data_array[0];
	$phone_numbers=explode('###', $data_array[1]);
        $from = $phone_numbers[0];
        $to   = $phone_numbers[1];
        $user_phone_words = chunk_split($from, 1, " ");

}
header("content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>

<Response>
   <Say voice="woman"><?php echo $user_data?></Say>
</Response>
