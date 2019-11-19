<?php 

    //$sourceLocation = isset($_REQUEST["sourceLocation"]) ? $_REQUEST["sourceLocation"] : '';
    //$destinationLocation = isset($_REQUEST["destinationLocation"]) ? $_REQUEST["destinationLocation"] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $distance = isset($_REQUEST["distance"]) ? $_REQUEST["distance"] : '';
    $time = isset($_REQUEST["time"]) ? $_REQUEST["time"] : '';
    $SelectedCar = isset($_REQUEST["SelectedCar"]) ? $_REQUEST["SelectedCar"] : '';
    $StartLatitude = isset($_REQUEST["StartLatitude"]) ? $_REQUEST["StartLatitude"] : '0.0';
    $EndLongitude = isset($_REQUEST["EndLongitude"]) ? $_REQUEST["EndLongitude"] : '0.0';
    $DestLatitude = isset($_REQUEST["DestLatitude"]) ? $_REQUEST["DestLatitude"] : '';
    $DestLongitude = isset($_REQUEST["DestLongitude"]) ? $_REQUEST["DestLongitude"] : '';
    $promoCode = isset($_REQUEST['PromoCode']) ? clean($_REQUEST['PromoCode']) : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST['UserType'] : '';
    $GeneralUserType = isset($_REQUEST['GeneralUserType']) ? trim($_REQUEST['GeneralUserType']) : '';
    $isDestinationAdded = isset($_REQUEST['isDestinationAdded']) ? trim($_REQUEST['isDestinationAdded']) : 'Yes'; // Yes , No
    if ($userType == "" || $userType == null) {
        $userType = $GeneralUserType;
    }
    if ($isDestinationAdded == "" || $isDestinationAdded == null) {
        $isDestinationAdded = "Yes";
    }

    ######### Checking For Flattrip #########
    if ($isDestinationAdded == "Yes") {
        $sourceLocationArr = array($StartLatitude, $EndLongitude);
        $destinationLocationArr = array($DestLatitude, $DestLongitude);
        $data_flattrip = checkFlatTripnew($sourceLocationArr, $destinationLocationArr, $SelectedCar);
        $eFlatTrip = $data_flattrip['eFlatTrip'];
        $fFlatTripPrice = $data_flattrip['Flatfare'];
    } else {
        $eFlatTrip = "No";
        $fFlatTripPrice = 0;
    }
    ######### Checking For Flattrip #########

    $curr_date = @date("Y-m-d");
    $time = round(($time / 60), 2);
    $distance = round(($distance / 1000), 2);
    $Fare_data = calculateFareEstimateAll($time, $distance, $SelectedCar, $iUserId, 1, "", "", $promoCode, 1, 0, 0, 0, "DisplySingleVehicleFare", $userType, 1, "", $isDestinationAdded, $eFlatTrip, $fFlatTripPrice);
    /*
    if($eFlatTrip == "No") {
    $curr_date=@date("Y-m-d");
    $time = round(($time / 60),2);
    $distance = round(($distance / 1000),2);
    $Fare_data=calculateFareEstimateAll($time,$distance,$SelectedCar,$iUserId,1,"","",$promoCode,1,0,0,0,"DisplySingleVehicleFare",$userType,1,"",$isDestinationAdded,$eFlatTrip,$fFlatTripPrice);
    }else{
    if($userType == "Passenger") {
    $vCurrencyPassenger=get_value('register_user', 'vCurrencyPassenger', 'iUserId', $iUserId,'','true');
    $userlangcode = get_value("register_user", "vLang", "iUserId", $iUserId, '', 'true');
    }else{
    $vCurrencyPassenger=get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $iUserId,'','true');
    $userlangcode = get_value("register_driver", "vLang", "iDriverId", $iUserId, '', 'true');
    }
    if ($vCurrencyPassenger == "" || $vCurrencyPassenger == NULL) {
    $vCurrencyPassenger = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    }
    $priceRatio=get_value('currency', 'Ratio', 'vName', $vCurrencyPassenger,'','true');
    $vSymbol=get_value('currency', 'vSymbol', 'vName', $vCurrencyPassenger,'','true');
    if($userlangcode == "" || $userlangcode == NULL) {
    $userlangcode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $languageLabelsArr = getLanguageLabelsArr($userlangcode, "1");
    $Fare_data[0]['total_fare'] = round($fFlatTripPrice * $priceRatio, 2);
    $Fare_data[0][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = $vSymbol." ".$Fare_data[0]['total_fare'];
    }     */
    $returnArr["Action"] = "1";
    $returnArr["message"] = $Fare_data;
    echo json_encode($returnArr);

?>