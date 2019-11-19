<?php 
    global $generalobj, $obj;
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';

    $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    if ($vLangCode == "" || $vLangCode == null) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $Data1 = array();
    if ($iUserId != "") {
        $sql1 = "SELECT rd.iDriverId,rd.vImage as driverImage,concat(rd.vName,' ',rd.vLastName) as driverName, rd.vCode ,rd.vPhone as driverMobile ,rd.vLatitude as driverLatitude,rd.vLongitude as driverLongitude,rd.vTripStatus as driverStatus, rd.vAvgRating as driverRating, tr.`vRideNo`, tr.tSaddress,tr.iTripId, tr.iVehicleTypeId,tr.tTripRequestDate,tr.eFareType,tr.vTimeZone from trips as tr
			LEFT JOIN register_driver as rd ON rd.iDriverId=tr.iDriverId
			WHERE tr.iActive != 'Canceled' AND iActive != 'Finished' AND iUserId='" . $iUserId . "' AND eType = 'UberX' ORDER BY tr.iTripId DESC";

        $Data1 = $obj->MySQLSelect($sql1);
        if (count($Data1) > 0) {
            for ($i = 0; $i < count($Data1); $i++) {
                $iVehicleCategoryId = get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId', $Data1[$i]['iVehicleTypeId'], '', 'true');
                $vVehicleTypeName = get_value('vehicle_type', 'vVehicleType_' . $vLangCode, 'iVehicleTypeId', $Data1[$i]['iVehicleTypeId'], '', 'true');
                if ($iVehicleCategoryId != 0) {
                    $vVehicleCategoryName = get_value('vehicle_category', 'vCategory_' . $vLangCode, 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
                    $vVehicleTypeName = $vVehicleCategoryName . "-" . $vVehicleTypeName;
                }
                $Data1[$i]['SelectedTypeName'] = $vVehicleTypeName;
                // Convert Into Timezone
                $tripTimeZone = $Data1[$i]['vTimeZone'];
                if ($tripTimeZone != "") {
                    $serverTimeZone = date_default_timezone_get();
                    $Data1[$i]['tTripRequestDate'] = converToTz($Data1[$i]['tTripRequestDate'], $tripTimeZone, $serverTimeZone);
                }
                // Convert Into Timezone
                $Data1[$i]['dDateOrig'] = $Data1[$i]['tTripRequestDate'];
            }
            $returnArr['Action'] = "1";
            $returnArr['SERVER_TIME'] = date('Y-m-d H:i:s');
            $returnArr['message'] = $Data1;
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_NO_DATA_AVAIL";
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DATA_AVAIL";
    }
    echo json_encode($returnArr);

?>