<?php 


    $driver_id_auto = isset($_REQUEST["DautoId"]) ? $_REQUEST["DautoId"] : '';
    $user_id_auto = isset($_REQUEST["UautoId"]) ? $_REQUEST["UautoId"] : '';
    $message_rec = isset($_REQUEST["message_rec"]) ? $_REQUEST["message_rec"] : '';
    $message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : '';
    $tripID = isset($_REQUEST["tripID"]) ? $_REQUEST["tripID"] : '';

    $sender_type = "Driver";

    $Data_update_Messages['tMessage'] = $message;
    $Data_update_Messages['tSendertype'] = $sender_type;
    $Data_update_Messages['iTripId'] = $tripID;

    $id = $obj->MySQLQueryPerform("driver_user_messages", $Data_update_Messages, 'insert');

    $message_new_combine = $message_rec . $message;

    $sql = "SELECT iGcmRegId FROM register_user WHERE iUserId='$user_id_auto'  AND eDeviceType = 'Android'";
    $result = $obj->MySQLSelect($sql);

    $registatoin_ids = $result[0]['iGcmRegId'];

    $Rregistatoin_ids = array(
        $registatoin_ids,
    );
    $Rmessage = array(
        "message" => $message_new_combine,
    );
    $result = send_notification($Rregistatoin_ids, $Rmessage);

    echo $result;


?>