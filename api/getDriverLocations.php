<?php 

    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';

    $sql = "SELECT vLatitude, vLongitude,vTripStatus FROM `register_driver` WHERE iDriverId='$iDriverId'";
    $Data = $obj->MySQLSelect($sql);

    if (count($Data) == 1) {$returnArr['Action'] = "1";
        $returnArr['vLatitude'] = $Data[0]['vLatitude'];
        $returnArr['vLongitude'] = $Data[0]['vLongitude'];
        $returnArr['vTripStatus'] = $Data[0]['vTripStatus'];
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = 'Not Found';
    }
    echo json_encode($returnArr);


?>