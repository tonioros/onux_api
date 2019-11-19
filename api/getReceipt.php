<?php 


    $iTripId = isset($_REQUEST['iTripId']) ? clean($_REQUEST['iTripId']) : '';
    $UserType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : ''; //Passenger OR Driver

    $value = sendTripReceipt($iTripId);

    if ($value == true || $value == "true" || $value == "1") {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_CHECK_INBOX_TXT";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_FAILED_SEND_RECEIPT_EMAIL_TXT";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);
    exit;

?>