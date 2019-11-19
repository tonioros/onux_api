<?php 
    global $generalobj, $tconfig;

    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $memberType = isset($_REQUEST['MemberType']) ? clean($_REQUEST['MemberType']) : 'Driver';
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : 'Ride'; //'Ride', 'Delivery', 'UberX'

    $ssql = "";
    if ($APP_TYPE == "Delivery") {
        $ssql .= " AND dv.eType = 'Delivery'";
    } else if ($APP_TYPE == "Ride-Delivery") {
        //$ssql.= " AND ( dv.eType = 'Delivery' OR dv.eType = 'Ride')";
        $ssql .= " AND dv.eType = '" . $eType . "'";
    } else if ($APP_TYPE == "Ride-Delivery-UberX") {
        //$ssql.= " AND ( dv.Type = 'Delivery' OR dv.eType = 'Ride' OR dv.eType = 'UberX')";
        $ssql .= " AND dv.eType = '" . $eType . "'";
    } else {
        $ssql .= " AND dv.eType = '" . $APP_TYPE . "'";
    }

    $sql = "select iCompanyId from `register_driver` where iDriverId = '" . $iMemberId . "'";
    $db_usr = $obj->MySQLSelect($sql);
    $iCompanyId = $db_usr[0]['iCompanyId'];
    //$APP_TYPE = $generalobj->getConfigurations("configurations", "APP_TYPE");

    if ($APP_TYPE == 'UberX') {
        $sql = "SELECT * FROM driver_vehicle where iCompanyId = '" . $iCompanyId . "' and iDriverId = '" . $iMemberId . "' and eStatus != 'Deleted'";
        $db_vehicle = $obj->MySQLSelect($sql);
    } else {

        $sql = "SELECT m.vTitle, mk.vMake,dv.* ,case WHEN (dv.vInsurance='' OR dv.vPermit='' OR dv.vRegisteration='') THEN 'TRUE' ELSE 'FALSE' END as 'VEHICLE_DOCUMENT'
			FROM driver_vehicle as dv JOIN model m ON dv.iModelId=m.iModelId JOIN make mk ON dv.iMakeId=mk.iMakeId where iCompanyId = '" . $iCompanyId . "' and iDriverId = '" . $iMemberId . "' and dv.eStatus != 'Deleted' $ssql Order By dv.iDriverVehicleId desc";
        // echo   $sql = "SELECT m.vTitle, mk.vMake,dv.*  FROM driver_vehicle as dv JOIN model m ON dv.iModelId=m.iModelId JOIN make mk ON dv.iMakeId=mk.iMakeId where iCompanyId = '" . $iCompanyId . "' and iDriverId = '" . $iMemberId . "' and dv.eStatus != 'Deleted'";
        $db_vehicle = $obj->MySQLSelect($sql);
        $db_vehicle_new = $db_vehicle;
        for ($i = 0; $i < count($db_vehicle); $i++) {
            $vCarType = $db_vehicle[$i]['vCarType'];
            $sql = "SELECT iVehicleTypeId,eType  FROM `vehicle_type` WHERE `iVehicleTypeId` IN ($vCarType)";
            $db_cartype = $obj->MySQLSelect($sql);
            $k = 0;
            if (count($db_cartype) > 0) {
                for ($j = 0; $j < count($db_cartype); $j++) {
                    $eType = $db_cartype[$j]['eType'];
                    if ($eType == "UberX") {
                        //unset($db_vehicle_new[$i]);
                    }
                }
            }
        }
    }
    $db_vehicle_new = array_values($db_vehicle_new);
    if (count($db_vehicle_new) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $db_vehicle_new;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_VEHICLES_FOUND";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);
    exit;

?>