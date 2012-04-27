<?php
if($_REQUEST['Digits'] != '1' && $_REQUEST['Digits'] != '0' && $_REQUEST['Digits'] != '4'&& $_REQUEST['Digits'] != '6') die();

    $data=base64_decode($_REQUEST['data']);
    $data_array=array();
    $data_array=explode('@#@#@@#',$data);
    $user_data=$data_array[0];
    $user_phone=$data_array[1];    
    $user_phone_words = chunk_split($user_phone,1," ");

    $caller_id = "";
    if (!empty($data_array[2])) {
      $caller_id = trim($data_array[2]);
    }

    $tmp = explode(".",$user_data);
    $datax = "";

    foreach($tmp as $str)
        $datax .= "<Say voice='woman'>$str</Say><Pause length='1'/>";

    header("content-type: text/xml");
?>
<Response>
    <?php if($_REQUEST['Digits'] == '1'): ?>
        <Say voice="woman">Connecting</Say><Dial<?php if(strlen($caller_id)>0) echo ' callerId="'.$caller_id.'"'; ?>><?php echo $user_phone;?></Dial>
    <?php elseif($_REQUEST['Digits'] == '0'): ?>
        <Gather numDigits="1" action="complete_call.php?data=<?php echo $_REQUEST['data']; ?>"><Say>A customer at the number <?php echo $user_phone_words?> is calling.</Say><Pause length="1"/><?php echo $datax; ?></Gather>
    <?php elseif($_REQUEST['Digits'] == '6'):
            include_once 'save_delay_call.php';
    ?>
    <Say voice="woman">We will re-try this call again in five minutes time, Thank You</Say>
    <?php elseif($_REQUEST['Digits'] == '4'): ?>
        <Gather numDigits="10" action="forward_call.php?data=<?php echo $_REQUEST['data']; ?>"><Say>Please enter a 10 digit number to redirect the call</Say></Gather>
    <?php endif; ?>
</Response>