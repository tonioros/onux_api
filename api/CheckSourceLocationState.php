<?php 
    global $generalobj, $tconfig;
    $PickUpLatitude = isset($_REQUEST["PickUpLatitude"]) ? $_REQUEST["PickUpLatitude"] : '0.0';
    $PickUpLongitude = isset($_REQUEST["PickUpLongitude"]) ? $_REQUEST["PickUpLongitude"] : '0.0';
    $selectedCarTypeID = isset($_REQUEST["SelectedCarTypeID"]) ? $_REQUEST["SelectedCarTypeID"] : '';
    //$APP_TYPE = $generalobj->getConfigurations("configurations","APP_TYPE");
    $CurrentCabGeneralType = isset($_REQUEST["CurrentCabGeneralType"]) ? $_REQUEST["CurrentCabGeneralType"] : '';
    $APP_TYPE = $CurrentCabGeneralType;

    if ($APP_TYPE == "Delivery" || $APP_TYPE == "Deliver") {
        $ssql .= " AND eType = 'Deliver'";
    } else if ($APP_TYPE == "Ride-Delivery" || $APP_TYPE == "Ride-Deliver") {
        $ssql .= " AND ( eType = 'Deliver' OR eType = 'Ride')";
    } else if ($APP_TYPE == "Ride-Delivery-UberX" || $APP_TYPE == "Ride-Deliver-UberX") {
        $ssql .= " AND ( eType = 'Deliver' OR eType = 'Ride' OR eType = 'UberX')";
    } else {
        $ssql .= " AND eType = '" . $APP_TYPE . "'";
    }

    $pickuplocationarr = array($PickUpLatitude, $PickUpLongitude);
    $allowed_ans = checkAllowedAreaNew($pickuplocationarr, "No");
    if ($allowed_ans == "No") {
        $returnArr['Action'] = "1";
        $obj->MySQLClose();
        echo json_encode($returnArr);exit;
    }
    $GetVehicleIdfromGeoLocation = GetVehicleTypeFromGeoLocation($pickuplocationarr);
    //$sql23 = "SELECT iVehicleTypeId FROM `vehicle_type` WHERE iLocationid IN ($GetVehicleIdfromGeoLocation) AND iVehicleTypeId IN ($selectedCarTypeID) ORDER BY iVehicleTypeId ASC";
    $sql23 = "SELECT iVehicleTypeId FROM `vehicle_type` WHERE iLocationid IN ($GetVehicleIdfromGeoLocation) $ssql ORDER BY iVehicleTypeId ASC";
    $vehicleTypes = $obj->MySQLSelect($sql23);
    $Vehicle_Str = "";
    if (count($vehicleTypes) > 0) {
        for ($i = 0; $i < count($vehicleTypes); $i++) {
            $Vehicle_Str .= $vehicleTypes[$i]['iVehicleTypeId'] . ",";
        }
        $Vehicle_Str = substr($Vehicle_Str, 0, -1);
    }
    $selectedCarTypeID_Arr = explode(",", $selectedCarTypeID);
    $Vehicle_Str_Arr = explode(",", $Vehicle_Str);
    if ($selectedCarTypeID_Arr === array_intersect($selectedCarTypeID_Arr, $Vehicle_Str_Arr) && $Vehicle_Str_Arr === array_intersect($Vehicle_Str_Arr, $selectedCarTypeID_Arr)) {
        $returnArr['Action'] = "0";
    } else {
        $returnArr['Action'] = "1";
    }

    $obj->MySQLClose();
    echo json_encode($returnArr);exit;

?>