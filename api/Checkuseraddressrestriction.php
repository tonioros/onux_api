<?php 
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $iUserAddressId = isset($_REQUEST["iUserAddressId"]) ? $_REQUEST["iUserAddressId"] : '';
    $iSelectVehicalId = isset($_REQUEST["iSelectVehicalId"]) ? $_REQUEST["iSelectVehicalId"] : '';

    $sql = "SELECT vLatitude,vLongitude FROM user_address WHERE iUserAddressId='" . $iUserAddressId . "'";
    $address_data = $obj->MySQLSelect($sql);
    if (count($address_data) > 0) {
        $StartLatitude = $address_data[0]['vLatitude'];
        $EndLongitude = $address_data[0]['vLongitude'];
        $pickuplocationarr = array($StartLatitude, $EndLongitude);
        //$allowed_ans = checkRestrictedAreaNew($pickuplocationarr,"No");
        $allowed_ans = checkAllowedAreaNew($pickuplocationarr, "No");
        if ($allowed_ans == "Yes") {
            $GetVehicleIdfromGeoLocation = GetVehicleTypeFromGeoLocation($pickuplocationarr);
            $sql23 = "SELECT iVehicleTypeId FROM `vehicle_type` WHERE iLocationid IN ($GetVehicleIdfromGeoLocation) ORDER BY iVehicleTypeId ASC";
            $vehicleTypes = $obj->MySQLSelect($sql23);
            $Vehicle_Str = "";
            if (count($vehicleTypes) > 0) {
                for ($i = 0; $i < count($vehicleTypes); $i++) {
                    $Vehicle_Str .= $vehicleTypes[$i]['iVehicleTypeId'] . ",";
                }
                $Vehicle_Str = substr($Vehicle_Str, 0, -1);
            }
            $Vehicle_Str_Arr = explode(",", $Vehicle_Str);
            if (in_array($iSelectVehicalId, $Vehicle_Str_Arr)) {
                $returnArr['Action'] = "1";
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_NO_SERVICES_AVAIL_FOR_JOB_LOC";
            }
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_JOB_LOCATION_NOT_ALLOWED";
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_JOB_LOCATION_NOT_ALLOWED";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);exit;

?>