<?php 
    global $generalobj, $obj;
    $TripId = isset($_REQUEST['TripId']) ? clean($_REQUEST['TripId']) : '';
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $iHelpDetailId = isset($_REQUEST['iHelpDetailId']) ? clean($_REQUEST['iHelpDetailId']) : '';
    $vComment = isset($_REQUEST['vComment']) ? clean($_REQUEST['vComment']) : '';
    $appType = isset($_REQUEST['appType']) ? clean($_REQUEST['appType']) : '';
    $current_date = date('Y-m-d H:i:s');
    if ($appType == "Driver") {
        $sql = "SELECT CONCAT(vName,' ',vLastName) as Name FROM `register_driver` WHERE iDriverId='" . $iMemberId . "'";
    } else {
        $sql = "SELECT CONCAT(vName,' ',vLastName) as Name FROM `register_user` WHERE iUserId='" . $iMemberId . "'";
    }
    $Data = $obj->MySQLSelect($sql);
    $Data_trip_help_detail['iTripId'] = $TripId;
    $Data_trip_help_detail['iUserId'] = $iMemberId;
    $Data_trip_help_detail['iHelpDetailId'] = $iHelpDetailId;
    $Data_trip_help_detail['vComment'] = $vComment;
    $Data_trip_help_detail['tDate'] = $current_date;
    $id = $obj->MySQLQueryPerform('trip_help_detail', $Data_trip_help_detail, 'insert');
    if ($id > 0) {
        $vRideNo = get_value('trips', 'vRideNo', 'iTripId', $TripId, '', 'true');
        $maildata['iTripId'] = $vRideNo;
        $maildata['NAME'] = $Data[0]['Name'];
        $maildata['vComment'] = $vComment;
        $maildata['Ddate'] = $current_date;
        $generalobj->send_email_user("RIDER_TRIP_HELP_DETAIL", $maildata);
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_COMMENT_ADDED_TXT";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    echo json_encode($returnArr);
    exit;

?>