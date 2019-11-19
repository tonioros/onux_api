<?php 
    $iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? $_REQUEST['iVehicleCategoryId'] : '';
    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
    $languageCode = "";
    if ($iDriverId != "") {
        $languageCode = get_value('register_driver', 'vLang', 'iDriverId', $iDriverId, '', 'true');
    }
    if ($languageCode == "" || $languageCode == null) {
        $languageCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $sql = "SELECT * FROM `register_driver` where iDriverId ='" . $iDriverId . "'";
    $db_driverdetail = $obj->MySQLSelect($sql);
    $vCountry = $db_driverdetail[0]['vCountry'];
    $languageLabelsArr = getLanguageLabelsArr($languageCode, "1");
    $lbl_all = $languageLabelsArr['LBL_ALL'];
    $ssql = "";
    if ($vCountry != "") {
        $iCountryId = get_value('country', 'iCountryId', 'vCountryCode', $vCountry, '', 'true');
        $sql = "SELECT * FROM location_master WHERE eStatus='Active' AND iCountryId = '" . $iCountryId . "' AND eFor = 'VehicleType'";
        $db_country = $obj->MySQLSelect($sql);
        $country_str = "-1";
        if (count($db_country) > 0) {
            for ($i = 0; $i < count($db_country); $i++) {
                $country_str .= "," . $db_country[$i]['iLocationId'];
            }
        }
        $ssql .= " AND iLocationid IN ($country_str) ";
    }
    $sql2 = "SELECT iVehicleTypeId, vVehicleType_" . $languageCode . " as vTitle,eFareType,eAllowQty,iMaxQty,fFixedFare,fPricePerHour,iLocationid from vehicle_type where iVehicleCategoryId in($iVehicleCategoryId)" . $ssql;
    $vehicleDetail = $obj->MySQLSelect($sql2);
    $vCurrencyDriver = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $db_driverdetail[0]['iDriverId'], '', 'true');
    if ($vCurrencyDriver == "" || $vCurrencyDriver == null) {
        $vCurrencyDriver = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    }
    $vCurrencyData = get_value('currency', 'vSymbol, Ratio', 'vName', $vCurrencyDriver);
    $vCurrencySymbol = $vCurrencyData[0]['vSymbol'];
    $vCurrencyRatio = $vCurrencyData[0]['Ratio'];
    $iParentId = get_value('vehicle_category', 'iParentId', 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
    if ($iParentId == 0) {
        $ePriceType = get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
    } else {
        $ePriceType = get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
    }
    //$iDriverVehicleId = get_value('register_driver', 'iDriverVehicleId', 'iDriverId',$iDriverId,'','true');
    if ($APP_TYPE == "Ride-Delivery-UberX") {
        $query = "SELECT iDriverVehicleId FROM `driver_vehicle` WHERE iDriverId = '" . $iDriverId . "' AND eType = 'UberX' ORDER BY iDriverVehicleId DESC LIMIT 0,1";
        $result = $obj->MySQLSelect($query);
        $iDriverVehicleId = $result[0]['iDriverVehicleId'];
    } else {
        $iDriverVehicleId = get_value('register_driver', 'iDriverVehicleId', 'iDriverId', $iDriverId, '', 'true');
    }

    $sql = "SELECT vCarType FROM `driver_vehicle` where iDriverId ='" . $iDriverId . "' AND iDriverVehicleId = '" . $iDriverVehicleId . "'";
    $db_vCarType = $obj->MySQLSelect($sql);
    if (count($db_vCarType) > 0) {
        $vehicle_service_id = explode(",", $db_vCarType[0]['vCarType']);
        for ($i = 0; $i < count($vehicleDetail); $i++) {
            $sql3 = "SELECT * FROM `service_pro_amount` where iDriverVehicleId ='" . $iDriverVehicleId . "' AND iVehicleTypeId='" . $vehicleDetail[$i]['iVehicleTypeId'] . "'";
            $db_serviceproviderid = $obj->MySQLSelect($sql3);
            if (count($db_serviceproviderid) > 0) {
                $vehicleDetail[$i]['fAmount'] = $db_serviceproviderid[0]['fAmount'];
            } else {
                if ($vehicleDetail[$i]['eFareType'] == "Hourly") {
                    $vehicleDetail[$i]['fAmount'] = $vehicleDetail[$i]['fPricePerHour'];
                } else {
                    $vehicleDetail[$i]['fAmount'] = $vehicleDetail[$i]['fFixedFare'];
                }
            }
            // $vehicleDetail[$i]['iDriverVehicleId']=$db_driverdetail[0]['iDriverVehicleId'];
            $fAmount = round($vehicleDetail[$i]['fAmount'] * $vCurrencyRatio, 2);
            $vehicleDetail[$i]['fAmount'] = $fAmount;
            $vehicleDetail[$i]['ePriceType'] = $ePriceType;
            $vehicleDetail[$i]['vCurrencySymbol'] = $vCurrencySymbol;
            $data_service[$i] = $vehicleDetail[$i];
            if (in_array($data_service[$i]['iVehicleTypeId'], $vehicle_service_id)) {
                $vehicleDetail[$i]['VehicleServiceStatus'] = 'true';
            } else {
                $vehicleDetail[$i]['VehicleServiceStatus'] = 'false';
            }
            if ($vehicleDetail[$i]['iLocationid'] == "-1") {
                $vehicleDetail[$i]['SubTitle'] = $lbl_all;
            } else {
                $sql = "SELECT vLocationName FROM location_master WHERE iLocationId = '" . $vehicleDetail[$i]['iLocationid'] . "'";
                $locationname = $obj->MySQLSelect($sql);
                $vehicleDetail[$i]['SubTitle'] = $locationname[0]['vLocationName'];
            }
        }
    }
    if (count($vehicleDetail) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $vehicleDetail;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DATA_AVAIL";
    }
    echo json_encode($returnArr);
