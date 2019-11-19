<?php 
    global $generalobj, $tconfig;

    $iUserId = isset($_REQUEST['iUserId']) ? clean($_REQUEST['iUserId']) : '';
    $sql = "SELECT m.vTitle, mk.vMake,uv.*  FROM user_vehicle as uv JOIN model m ON uv.iModelId=m.iModelId JOIN make mk ON uv.iMakeId=mk.iMakeId where iUserId = '" . $iUserId . "' and uv.eStatus != 'Deleted'";
    $db_vehicle = $obj->MySQLSelect($sql);

    if (count($db_vehicle) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $db_vehicle;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "No Vehicles Found";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);
    exit;

?>