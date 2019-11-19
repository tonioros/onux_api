<?php 

    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $latitude_driver = isset($_REQUEST["latitude"]) ? $_REQUEST["latitude"] : '';
    $longitude_driver = isset($_REQUEST["longitude"]) ? $_REQUEST["longitude"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';

    $where = " iDriverId='$iDriverId'";
    $Data_update_driver['vLatitude'] = $latitude_driver;
    $Data_update_driver['vLongitude'] = $longitude_driver;

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
    # Update User Location Date #
    Updateuserlocationdatetime($iDriverId, "Driver", $vTimeZone);
    # Update User Location Date #

    if ($id) {
        $returnArr['Action'] = "1";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    echo json_encode($returnArr);

?>