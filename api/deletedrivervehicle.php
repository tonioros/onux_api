<?php
    global $generalobj, $tconfig, $obj;
    $returnArr = array();
    $iMemberCarId = isset($_REQUEST['iDriverVehicleId']) ? clean($_REQUEST['iDriverVehicleId']) : '';
    $iDriverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';
    // getLanguageCode($iMemberId); //create array of language_label
    $iDriverVehicleId = get_value('register_driver', 'iDriverVehicleId', 'iDriverId', $iDriverId, '', 'true');
    if ($iDriverVehicleId == $iMemberCarId) {
        $returnArr['Action'] = 0;
        $returnArr['message'] = "LBL_DELETE_VEHICLE_ERROR";
        echo json_encode($returnArr);
        exit;
    }

    //$sql = "DELETE FROM driver_vehicle WHERE iDriverVehicleId='" . $iMemberCarId . "' AND iDriverId='" . $iDriverId . "'";
    $sql = "UPDATE driver_vehicle set eStatus='Deleted' WHERE iDriverVehicleId='" . $iMemberCarId . "' AND iDriverId = '" . $iDriverId . "'";
    $db_sql = $obj->sql_query($sql);
    //if (mysql_affected_rows() > 0) {
    if ($obj->GetAffectedRows() > 0) {
        $returnArr['Action'] = 1;
        $returnArr['message'] = "LBL_DELETE_VEHICLE";
    } else {
        $returnArr['Action'] = 0;
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);
    exit;

?>