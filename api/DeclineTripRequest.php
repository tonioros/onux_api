<?php 
    $passenger_id = isset($_REQUEST["PassengerID"]) ? $_REQUEST["PassengerID"] : '';
    $driver_id = isset($_REQUEST["DriverID"]) ? $_REQUEST["DriverID"] : '';
    $vMsgCode = isset($_REQUEST["vMsgCode"]) ? $_REQUEST["vMsgCode"] : '';

    $sql = "SELECT iDriverRequestId,eAcceptAttempted FROM `driver_request` WHERE iDriverId = '" . $driver_id . "' AND iUserId = '" . $passenger_id . "' AND iTripId = '0' AND vMsgCode='" . $vMsgCode . "' AND eAcceptAttempted = 'No'";
    $db_sql = $obj->MySQLSelect($sql);
    if (count($db_sql) > 0) {
        $request_count = UpdateDriverRequest2($driver_id, $passenger_id, "0", "Decline", $vMsgCode, "No");
    } else {
        $request_count = 0;
    }

    echo $request_count;

?>