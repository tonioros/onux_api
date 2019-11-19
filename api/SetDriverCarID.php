<?php 

    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $Data['iDriverVehicleId'] = isset($_REQUEST["iDriverVehicleId"]) ? $_REQUEST["iDriverVehicleId"] : '';

    $where = " iDriverId = '" . $iDriverId . "'";

    $sql = $obj->MySQLQueryPerform("register_driver", $Data, 'update', $where);
    if ($sql > 0) {
        $returnArr['Action'] = "1";
        echo json_encode($returnArr);
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        echo json_encode($returnArr);
    }

?>