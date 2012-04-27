<?php
    header("content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<Response>
    <Pause length="1"/><Say voice="woman">A customer at the number </Say><Pause length="1"/><Say voice="woman"><?php echo @$_REQUEST['number']?> has submitted an inquiry.</Say>
    <Dial><?php echo $_REQUEST['number']?></Dial>
</Response>