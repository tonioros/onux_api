<?php 
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $iFromMemberId = isset($_REQUEST["iFromMemberId"]) ? $_REQUEST["iFromMemberId"] : '';
    $iToMemberId = isset($_REQUEST['iToMemberId']) ? clean($_REQUEST['iToMemberId']) : '';
    $iTripId = isset($_REQUEST['iTripId']) ? clean($_REQUEST['iTripId']) : '';
    $tMessage = isset($_REQUEST['tMessage']) ? stripslashes($_REQUEST['tMessage']) : '';

    $Data['iTripId'] = $iTripId;
    $Data['iFromMemberId'] = $iFromMemberId;
    $Data['iToMemberId'] = $iToMemberId;
    $Data['tMessage'] = $tMessage;
    $Data['dAddedDate'] = @date("Y-m-d H:i:s");
    $Data['eStatus'] = "Unread";
    $Data['eUserType'] = $UserType;
    $id = $obj->MySQLQueryPerform('trip_messages', $Data, 'insert');
    if ($id > 0) {
        $returnArr['Action'] = "1";
        // $message = sendTripMessagePushNotification($iFromMemberId,$UserType,$iToMemberId,$iTripId,$tMessage);
        // if($message == 1){
        // $returnArr['Action'] ="1";
        // }else{
        // $returnArr['Action'] ="0";
        // $returnArr['message'] ="LBL_TRY_AGAIN_LATER_TXT";
        // }
        sendTripMessagePushNotification($iFromMemberId, $UserType, $iToMemberId, $iTripId, $tMessage);
        $obj->MySQLClose();
        echo json_encode($returnArr);
        exit;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        $obj->MySQLClose();
        echo json_encode($returnArr);
        exit;
    }

?>