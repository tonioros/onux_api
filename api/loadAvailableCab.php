<?php 

    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $passengerLat = isset($_REQUEST["PassengerLat"]) ? $_REQUEST["PassengerLat"] : '';
    $passengerLon = isset($_REQUEST["PassengerLon"]) ? $_REQUEST["PassengerLon"] : '';
    $iVehicleTypeId = isset($_REQUEST["iVehicleTypeId"]) ? $_REQUEST["iVehicleTypeId"] : '';
    $PickUpAddress = isset($_REQUEST["PickUpAddress"]) ? $_REQUEST["PickUpAddress"] : '';
    $geoCodeResult = isset($_REQUEST["currentGeoCodeResult"]) ? $_REQUEST["currentGeoCodeResult"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $scheduleDate = isset($_REQUEST["scheduleDate"]) ? $_REQUEST["scheduleDate"] : '';
    //$APP_TYPE = $generalobj->getConfigurations("configurations","APP_TYPE");
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : '';
    $eRental = isset($_REQUEST["eRental"]) ? $_REQUEST["eRental"] : 'No'; // Yes Or No
    $eShowOnlyMoto = isset($_REQUEST["eShowOnlyMoto"]) ? $_REQUEST["eShowOnlyMoto"] : 'No'; // Yes Or No
    $vCountryCode ='';
    if ($eRental == "" || $eRental == null) {
        $eRental = "No";
    }
    if ($eShowOnlyMoto == "" || $eShowOnlyMoto == null) {
        $eShowOnlyMoto = "No";
    }
    //$address_data = fetch_address_geocode($PickUpAddress,$geoCodeResult);
    $ssql='';
    if ($eType == "UberX" && $scheduleDate != "") {
        $Check_Driver_UFX = "Yes";
        $sdate = explode(" ", $scheduleDate);
        $shour = explode("-", $sdate[1]);
        $shour1 = $shour[0];
        $Check_Date_Time = $sdate[0] . " " . $shour1 . ":00:00";
    } else {
        $Check_Driver_UFX = "No";
        $Check_Date_Time = "";
    }

    $address_data['PickUpAddress'] = $PickUpAddress;

    $DataArr = getOnlineDriverArr($passengerLat, $passengerLon, $address_data, "No", "No", $Check_Driver_UFX, $Check_Date_Time, "", "", $eType);
    $Data = $DataArr['DriverList'];
    // print_r($Data);
    // die;

    //$ALLOW_SERVICE_PROVIDER_AMOUNT = $generalobj->getConfigurations("configurations","ALLOW_SERVICE_PROVIDER_AMOUNT");
    $iVehicleCategoryId = get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId', $iVehicleTypeId, '', 'true');
    $iParentId = get_value('vehicle_category', 'iParentId', 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
    if ($iParentId == 0) {
        $ePriceType = get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
    } else {
        $ePriceType = get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
    }
    //$ePriceType=get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId',$iVehicleCategoryId,'','true');
    $ALLOW_SERVICE_PROVIDER_AMOUNT = $ePriceType == "Provider" ? "Yes" : "No";

    /*$vLang=get_value('register_user', 'vLang', 'iUserId', $iUserId,'','true');
    $vCurrencyPassenger=get_value('register_user', 'vCurrencyPassenger', 'iUserId', $iUserId,'','true');
    $vCurrencySymbol=get_value('currency', 'vSymbol', 'vName', $vCurrencyPassenger,'','true');
    $priceRatio=get_value('currency', 'Ratio', 'vName', $vCurrencyPassenger,'','true');*/
    $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol,cu.Ratio FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iUserId . "'";
    $passengerData = $obj->MySQLSelect($sqlp);
    $vLang = $passengerData[0]['vLang'];
    $vCurrencyPassenger = $passengerData[0]['vCurrencyPassenger'];
    $vCurrencySymbol = $passengerData[0]['vSymbol'];
    $priceRatio = $passengerData[0]['Ratio'];
    $i = 0;
    while (count($Data) > $i) {
        if ($Data[$i]['vImage'] != "" && $Data[$i]['vImage'] != "NONE") {
            $Data[$i]['vImage'] = "3_" . $Data[$i]['vImage'];
        }
        $driverVehicleID = $Data[$i]['iDriverVehicleId'];
        if ($eType == "UberX") {
            $query = "SELECT iDriverVehicleId FROM `driver_vehicle` WHERE iDriverId = '" . $Data[$i]['iDriverId'] . "' AND eType = 'UberX'";
            $result = $obj->MySQLSelect($query);
            if (count($result) > 0) {
                $driverVehicleID = $result[0]['iDriverVehicleId'];
            }
        } else {
            $driverVehicleID = $Data[$i]['iDriverVehicleId'];
        }
        $Data[$i]['iDriverVehicleId'] = $driverVehicleID;
        $sql = "SELECT dv.*, make.vMake AS make_title, model.vTitle model_title FROM `driver_vehicle` dv, make, model
			WHERE dv.iMakeId = make.iMakeId
			AND dv.iModelId = model.iModelId
			AND iDriverVehicleId='$driverVehicleID'";
        $rows_driver_vehicle = $obj->MySQLSelect($sql);
        $fAmount = "";
        if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes") {
            $sqlServicePro = "SELECT * FROM `service_pro_amount` WHERE iDriverVehicleId='" . $rows_driver_vehicle[0]['iDriverVehicleId'] . "' AND iVehicleTypeId='" . $iVehicleTypeId . "'";
            $serviceProData = $obj->MySQLSelect($sqlServicePro);

            $vehicleTypeData = get_value('vehicle_type', 'eFareType,fPricePerHour,fFixedFare', 'iVehicleTypeId', $iVehicleTypeId);
            if ($vehicleTypeData[0]['eFareType'] == "Fixed") {
                $fAmount = $vCurrencySymbol . formatNum($vehicleTypeData[0]['fFixedFare'] * $priceRatio);
            } else if ($vehicleTypeData[0]['eFareType'] == "Hourly") {
                $fAmount = $vCurrencySymbol . formatNum($vehicleTypeData[0]['fPricePerHour'] * $priceRatio) . "/hour";
            }

            if (count($serviceProData) > 0) {
                $fAmount = formatNum($serviceProData[0]['fAmount'] * $priceRatio);
                if ($vehicleTypeData[0]['eFareType'] == "Fixed") {
                    $fAmount = $vCurrencySymbol . $fAmount;
                } else if ($vehicleTypeData[0]['eFareType'] == "Hourly") {
                    $fAmount = $vCurrencySymbol . $fAmount . "/hour";
                }
            }

            $rows_driver_vehicle[0]['fAmount'] = $fAmount;
            $rows_driver_vehicle[0]['vCurrencySymbol'] = $vCurrencySymbol;
        }

        $Data[$i]['DriverCarDetails'] = $rows_driver_vehicle[0];

        $i++;
    }
    $where = " iUserId='" . $iUserId . "'";
    $data['vLatitude'] = $passengerLat;
    $data['vLongitude'] = $passengerLon;
    $data['vRideCountry'] = $vCountryCode;
    $data['tLastOnline'] = @date("Y-m-d H:i:s");
    $obj->MySQLQueryPerform("register_user", $data, 'update', $where);
    # Update User Location Date #
    Updateuserlocationdatetime($iUserId, "Passenger", $vTimeZone);
    # Update User Location Date #

    $returnArr['AvailableCabList'] = $Data;
    $returnArr['PassengerLat'] = $passengerLat;
    $returnArr['PassengerLon'] = $passengerLon;

    if ($APP_TYPE == "Delivery") {
        $ssql .= " AND eType = 'Deliver'";
    } else if ($APP_TYPE == "Ride-Delivery") {
        $ssql .= " AND ( eType = 'Deliver' OR eType = 'Ride')";
    } else if ($APP_TYPE == "Ride-Delivery-UberX") {
        //$ssql.= " AND ( eType = 'Deliver' OR eType = 'Ride' OR eType = 'UberX')";
        $ssql .= " AND ( eType = 'Deliver' OR eType = 'Ride')";
    } else {
        $ssql .= " AND eType = '" . $APP_TYPE . "'";
    }
    if ($APP_TYPE == "Ride-Delivery-UberX") {
        $RideDeliveryIconArr = getGeneralVarAll_IconBanner();
        for ($i = 0; $i < count($RideDeliveryIconArr); $i++) {
            $vName = $RideDeliveryIconArr[$i]['vName'];
            $vValue = $RideDeliveryIconArr[$i]['vValue'];
            $$vName = $vValue;
        }
        if ($eShowOnlyMoto == "Yes") {
            $ssql .= " AND eIconType = 'Bike'";
        } else {
            if ($eRental == "No") {
                if ($MOTO_RIDE_SHOW_SELECTION == "None" || ($eRental == "Yes" && $MOTO_RENTAL_SHOW_SELECTION == "None") || $MOTO_DELIVERY_SHOW_SELECTION == "None") {
                    $ssql .= "";
                } else {
                    $ssql .= " AND eIconType != 'Bike'";
                }
            } else {
                if ($RENTAL_SHOW_SELECTION == "None" && $MOTO_RENTAL_SHOW_SELECTION == "None") {
                    $ssql .= "";
                } else {
                    $ssql .= " AND eIconType != 'Bike'";
                }
            }
        }
    }

    $pickuplocationarr = array($passengerLat, $passengerLon);
    $GetVehicleIdfromGeoLocation = GetVehicleTypeFromGeoLocation($pickuplocationarr);
    //$sql23 = "SELECT * FROM `vehicle_type` WHERE (iCityId='".$cityId."' OR iCityId = '-1') AND (iStateId='".$stateId."' OR iStateId = '-1') AND (iCountryId='".$countryId."' OR iCountryId = '-1') ORDER BY iVehicleTypeId ASC";
    $sql23 = "SELECT * FROM `vehicle_type` WHERE iLocationid IN ($GetVehicleIdfromGeoLocation) $ssql AND eStatus = 'Active' ORDER BY iVehicleTypeId ASC";
    $vehicleTypes = $obj->MySQLSelect($sql23);

    // $vehicleTypes = get_value('vehicle_type', '*', '', '',' ORDER BY iVehicleTypeId ASC');

    for ($i = 0; $i < count($vehicleTypes); $i++) {
        $Photo_Gallery_folder = $tconfig["tsite_upload_images_vehicle_type_path"] . '/' . $vehicleTypes[$i]['iVehicleTypeId'] . '/android/' . $vehicleTypes[$i]['vLogo'];
        if ($vehicleTypes[$i]['vLogo'] != "" && file_exists($Photo_Gallery_folder)) {
            $vehicleTypes[$i]['vLogo'] = $vehicleTypes[$i]['vLogo'];
        } else {
            $vehicleTypes[$i]['vLogo'] = "";
        }
        $vehicleTypes[$i]['fPricePerKM'] = round($vehicleTypes[$i]['fPricePerKM'] * $priceRatio, 2);
        $vehicleTypes[$i]['fPricePerMin'] = round($vehicleTypes[$i]['fPricePerMin'] * $priceRatio, 2);
        $vehicleTypes[$i]['iBaseFare'] = round($vehicleTypes[$i]['iBaseFare'] * $priceRatio, 2);
        $vehicleTypes[$i]['fCommision'] = round($vehicleTypes[$i]['fCommision'] * $priceRatio, 2);
        $vehicleTypes[$i]['iMinFare'] = round($vehicleTypes[$i]['iMinFare'] * $priceRatio, 2);
        $vehicleTypes[$i]['FareValue'] = round($vehicleTypes[$i]['fFixedFare'] * $priceRatio, 2);
        $vehicleTypes[$i]['vVehicleType'] = $vehicleTypes[$i]["vVehicleType_" . $vLang];
        /*Added For Rental*/
        if (ENABLE_RENTAL_OPTION == 'Yes' && $eType == "Ride") {
            if ($vehicleTypes[$i]["vRentalAlias_" . $vLang] != '') {
                $vehicleTypes[$i]['vRentalVehicleTypeName'] = $vehicleTypes[$i]["vRentalAlias_" . $vLang];
            } else {
                $vehicleTypes[$i]['vRentalVehicleTypeName'] = $vehicleTypes[$i]["vVehicleType_" . $vLang];
            }
            $checkrentalquery = "SELECT count(iRentalPackageId) as totalrental FROM  `rental_package` WHERE iVehicleTypeId = '" . $vehicleTypes[$i]['iVehicleTypeId'] . "'";
            $rental_data = $obj->MySQLSelect($checkrentalquery);
            if ($rental_data[0]['totalrental'] > 0) {
                $vehicleTypes[$i]['eRental'] = 'Yes';
            } else {
                $vehicleTypes[$i]['eRental'] = 'No';
            }
        } else {
            $vehicleTypes[$i]['eRental'] = 'No';
        }
        /*End Added For Rental*/
    }
    if ($eRental == "Yes") {
        $vehicleTypes_New = array();
        $vehicleTypes_New = $vehicleTypes;
        for ($i = 0; $i < count($vehicleTypes); $i++) {
            $isRemoveFromVehicleList = "Yes";
            $eRental = $vehicleTypes[$i]['eRental'];
            if ($eRental == "Yes") {
                $isRemoveFromVehicleList = "No";
            }
            if ($isRemoveFromVehicleList == "Yes") {
                unset($vehicleTypes_New[$i]);
            }
        }
        $vehicleTypes = array_values($vehicleTypes_New);
    }

    if ($eType == "UberX") {
        $returnArr['VehicleTypes'] = array();
    } else {
        $returnArr['VehicleTypes'] = $vehicleTypes;
    }
    //$returnArr['CurrentCity'] = $address_data['city'];
    //$returnArr['CurrentCountry'] = $address_data['country'];

    echo json_encode($returnArr);
