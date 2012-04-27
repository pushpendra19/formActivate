<?php
if(!empty($_REQUEST['data']))
{
	$data=base64_decode($_REQUEST['data']);
	$temp_data_array=array();
	$data_array=array();
	$temp_data_array = explode('@@@@',$data);
	$data_array=explode('@#@#@@#',$temp_data_array[0]);
	
	$to_repeat_announced = 1;
	if(count($temp_data_array)== 2)
		$to_repeat_announced = $temp_data_array[1];
		
	$user_data=$data_array[0];
	$user_phone_words = "";
	if(count($data_array) > 1)
	{
	$user_phone=$data_array[1];
	$user_phone_words = chunk_split($user_phone,1," ");	
	}
	
	
}


$tmp = explode(".",$user_data);
$datax = "";

for($i=1; $i<=$to_repeat_announced; $i++)
{
	$datax .="<Say voice='woman'>A customer at the number  $user_phone_words  has submitted an inquiry.</Say><Pause length='1'/>";
	foreach($tmp as $str) 
	{
	  $datax .= "<Say voice='woman'>$str</Say><Pause length='1'/>";
	}
	if($to_repeat_announced > 1)
		$datax .="<Pause length='9'/>";
}


header("content-type: text/xml");
?>
<Response><Gather numDigits="1" action="complete_call.php?data=<?php echo $_REQUEST['data']; ?>"><Pause length="1"/><?php echo $datax; ?></Gather></Response>
