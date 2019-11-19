<?php 
    $driverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Driver';
    $driver_detail = get_value('register_driver', 'vName,vLastName,vCurrencyDriver,vLang', 'iDriverId', $driverId);
    if ($APP_TYPE == "Ride-Delivery-UberX") {
        $sql = "SELECT * from trips WHERE iDriverId = '" . $driverId . "' AND eType != 'UberX' ORDER BY iTripId DESC LIMIT 0,1";
        //$sql = "SELECT * from trips WHERE iDriverId = '".$driverId."' AND eFareType NOT IN('Fixed', 'Hourly') ORDER BY iTripId DESC LIMIT 0,1";
    } else {
        $sql = "SELECT * from trips WHERE iDriverId = '" . $driverId . "' ORDER BY iTripId DESC LIMIT 0,1";
    }
    $tripData = $obj->MySQLSelect($sql);
    if (count($tripData) > 0) {
        $passenger_detail = get_value('register_user', 'vName,vLastName,eHail', 'iUserId', $tripData[0]['iUserId']);
        if ($passenger_detail[0]['eHail'] == "Yes") {
            $passengername = "--";
        } else {
            $passengername = $passenger_detail[0]['vName'] . " " . $passenger_detail[0]['vLastName'];
        }
        ## get fare details ##
        $vLang = $driver_detail[0]['vLang'];
        if ($vLang == "" || $vLang == null) {
            $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        $languageLabelsArr = getLanguageLabelsArr($vLang, "1");
        $vehicleTypes = get_value('vehicle_type', '*', 'iVehicleTypeId', $tripData[0]['iVehicleTypeId']);
        /*$priceRatio=get_value('currency', 'Ratio', 'vName', $driver_detail[0]['vCurrencyDriver'],'','true');
        $vCurrencySymbol=get_value('currency', 'vSymbol', 'vName', $driver_detail[0]['vCurrencyDriver'],'','true');*/
        $sql_request = "SELECT * FROM currency WHERE vName='" . $driver_detail[0]['vCurrencyDriver'] . "'";
        $drivercurrencydata = $obj->MySQLSelect($sql_request);
        $priceRatio = $drivercurrencydata[0]['Ratio'];
        $vCurrencySymbol = $drivercurrencydata[0]['vSymbol'];
        $eFareType = $vehicleTypes[0]['eFareType'];
        $eFlatTrip = $tripData[0]['eFlatTrip'];
        $fTripGenerateFare = $tripData[0]['fTripGenerateFare'];
        $fFlatTripPrice = $tripData[0]['fFlatTripPrice'];
        $fPricePerKM = round($vehicleTypes[0]['fPricePerKM'] * $priceRatio, 2);
        $fPricePerMin = round($vehicleTypes[0]['fPricePerMin'] * $priceRatio, 2);
        $iBaseFare = round($vehicleTypes[0]['iBaseFare'] * $priceRatio, 2);
        $fCommision = round($vehicleTypes[0]['fCommision'] * $priceRatio, 2);
        $iMinFare = round($vehicleTypes[0]['iMinFare'] * $priceRatio, 2);
        $fFixedFare = round($vehicleTypes[0]['fFixedFare'] * $priceRatio, 2);
        $fPricePerHour = round($vehicleTypes[0]['fPricePerHour'] * $priceRatio, 2);
        $fTripGenerateFare = round($fTripGenerateFare * $priceRatio, 2);
        $fFlatTripPrice = round($fFlatTripPrice * $priceRatio, 2);
        $iRentalPackageId = $tripData[0]['iRentalPackageId'];
        if ($iRentalPackageId > 0) {
            $PackageData = getRentalData($iRentalPackageId);
            $fPrice = $vCurrencySymbol . " " . round($PackageData[0]['fPrice'] * $priceRatio, 2);
            $pkgName = $PackageData[0]['vPackageName_' . $vLang];
            $Rate = $pkgName . " @ " . $fPrice;
        } else {
            if ($eFareType == "Regular") {
                $Rate = $vCurrencySymbol . " " . $iBaseFare . " " . $languageLabelsArr['LBL_BASE_FARE_SMALL_TXT'] . "+" . $vCurrencySymbol . " " . $fPricePerMin . " " . $languageLabelsArr['LBL_PRICE_PER_MINUTE_SMALL_TXT'] . "+" . $vCurrencySymbol . " " . $fPricePerKM . " " . $languageLabelsArr['LBL_PRICE_PER_KM_SMALL_TXT'];
            }
            if ($eFareType == "Fixed") {
                $Rate = $vCurrencySymbol . " " . $fFixedFare . " " . $languageLabelsArr['LBL_FIXED_FARE_TXT_ADMIN'];
            }
            if ($eFareType == "Hourly") {
                $Rate = $vCurrencySymbol . " " . $fPricePerHour . " " . $languageLabelsArr['LBL_PER_HOUR_SMALL_TXT'];
            }
            if ($eFlatTrip == "Yes") {
                if ($fTripGenerateFare > 0) {
                    //$Rate = $vCurrencySymbol." ".$fTripGenerateFare;
                    $Rate = $vCurrencySymbol . " " . $fFlatTripPrice;
                } else {
                    $Rate = $vCurrencySymbol . " " . $fFlatTripPrice;
                }

            }
        }
        ## get fare details ##
        $tripArr['DriverName'] = $driver_detail[0]['vName'] . " " . $driver_detail[0]['vLastName'];
        $tripArr['vRideNo'] = $tripData[0]['vRideNo'];
        $tripArr['tTripRequestDate'] = $tripData[0]['tTripRequestDate'];
        $tripArr['ProjectName'] = $SITE_NAME;
        $tripArr['tSaddress'] = $tripData[0]['tSaddress'];
        $tripArr['tDaddress'] = $tripData[0]['tDaddress'];
        $tripArr['PassengerName'] = $passengername;
        $tripArr['Licence_Plate'] = get_value('driver_vehicle', 'vLicencePlate', 'iDriverVehicleId', $tripData[0]['iDriverVehicleId'], '', 'true');
        $tripArr['PassengerCapacity'] = get_value('vehicle_type', 'iPersonSize', 'iVehicleTypeId', $tripData[0]['iVehicleTypeId'], '', 'true');
        // packagename changes
        //$tripArr['PackageName'] = get_value("package_type", "vName", "iPackageTypeId", $tripData[0]["iPackageTypeId"],"","true");
        $sql_request = "SELECT vName_" . $vLang . " as vName FROM package_type WHERE iPackageTypeId='" . $tripData[0]["iPackageTypeId"] . "'";
        $pkgdata = $obj->MySQLSelect($sql_request);
        $tripArr['PackageName'] = $pkgdata[0]['vName'];
        $tripArr['tPackageDetails'] = $tripData[0]['tPackageDetails'];
        $tripArr['vReceiverName'] = $tripData[0]['vReceiverName'];
        $tripArr['Rate'] = $Rate;
        $tripArr['eType'] = $tripData[0]['eType'];

        $returnArr['Action'] = "1";
        $returnArr['message'] = $tripArr;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DATA_AVAIL";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);exit;

?>