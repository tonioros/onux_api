<?php 

    global $generalobj, $tconfig, $LIST_DRIVER_LIMIT_BY_DISTANCE;
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $vWorkLocationRadius = isset($_REQUEST["vWorkLocationRadius"]) ? $_REQUEST["vWorkLocationRadius"] : '';
    $eStatus = isset($_REQUEST["eStatus"]) ? $_REQUEST["eStatus"] : 'Active';
    $Data_register_driver['vWorkLocationRadius'] = $vWorkLocationRadius;
    $eUnit = getMemberCountryUnit($iDriverId, "Driver");
    if ($eUnit == "Miles") {
        $Data_register_driver['vWorkLocationRadius'] = round($vWorkLocationRadius * 1.60934, 2); // convert miles to km
        $LIST_DRIVER_LIMIT_BY_DISTANCE = round($LIST_DRIVER_LIMIT_BY_DISTANCE * 0.621371, 2);
    } else {
        $Data_register_driver['vWorkLocationRadius'] = round($vWorkLocationRadius, 2); // convert miles to km
        $LIST_DRIVER_LIMIT_BY_DISTANCE = round($LIST_DRIVER_LIMIT_BY_DISTANCE, 2);
    }
    $where = " iDriverId = '" . $iDriverId . "'";
    $updateid = $obj->MySQLQueryPerform("register_driver", $Data_register_driver, 'update', $where);
    if ($updateid > 0) {
        $returnArr['Action'] = "1";
        $returnArr['UpdateId'] = $iDriverId;
        $returnArr['message'] = getDriverDetailInfo($iDriverId);
        $returnArr['message1'] = "LBL_INFO_UPDATED_TXT";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);
    exit;


?>