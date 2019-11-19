<?php 


    $deviceToken = $_REQUEST['Token'];
    $registation_ids_new = array();

    array_push($registation_ids_new, $deviceToken);

    $Rmessage = array("message" => $_REQUEST['message']);

    $result = send_notification($registation_ids_new, $Rmessage, 0);
    echo "<pre>";
    print_r($result);exit;


?>