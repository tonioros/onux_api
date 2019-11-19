<?php 
    global $generalobj, $tconfig;
    $iDriverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';
    $sql = "select vDay from `driver_manage_timing` where iDriverId = '" . $iDriverId . "' AND  vAvailableTimes <> '' ORDER BY iDriverTimingId DESC";
    $db_data = $obj->MySQLSelect($sql);
    if (count($db_data) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $db_data;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_AVAILABILITY_FOUND";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);
    exit;

?>