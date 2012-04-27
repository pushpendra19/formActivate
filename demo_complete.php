<?php
// if the caller pressed anything but 1

if($_REQUEST['Digits'] != '1') die();

$to = $_REQUEST['caller_number'];

// Otherwise, if 1 was pressed we Dial 3105551212. If 2
// we make an audio recording up to 30 seconds long.
header("content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>

<Response>
    <Say>Connecting your call</Say>
    <Dial><?php echo $to; ?></Dial>
</Response>