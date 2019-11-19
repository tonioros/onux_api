<?php 
    $selectedCarTypeID = isset($_REQUEST["SelectedCarTypeID"]) ? $_REQUEST["SelectedCarTypeID"] : '';
    $selectedTime = isset($_REQUEST["SelectedTime"]) ? $_REQUEST["SelectedTime"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $PickUpLatitude = isset($_REQUEST["PickUpLatitude"]) ? $_REQUEST["PickUpLatitude"] : '0.0';
    $PickUpLongitude = isset($_REQUEST["PickUpLongitude"]) ? $_REQUEST["PickUpLongitude"] : '0.0';
    $DestLatitude = isset($_REQUEST["DestLatitude"]) ? $_REQUEST["DestLatitude"] : '';
    $DestLongitude = isset($_REQUEST["DestLongitude"]) ? $_REQUEST["DestLongitude"] : '';
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    /*added for rental*/
    $iRentalPackageId = isset($_REQUEST["iRentalPackageId"]) ? $_REQUEST["iRentalPackageId"] : '';

    ######### Checking For Flattrip #########
    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $iUserId = "iUserId";
        $vCurrency = "vCurrencyPassenger";
        $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol,cu.Ratio FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iMemberId . "'";
        $passengerData = $obj->MySQLSelect($sqlp);
        $currencycode = $passengerData[0]['vCurrencyPassenger'];
        $currencySymbol = $passengerData[0]['vSymbol'];
        $priceRatio = $passengerData[0]['Ratio'];
    } else {
        $tblname = "register_driver";
        $iUserId = "iDriverId";
        $vCurrency = "vCurrencyDriver";
        $sqld = "SELECT rd.vCurrencyDriver,rd.vLang,cu.vSymbol,cu.Ratio FROM register_driver as rd LEFT JOIN currency as cu ON rd.vCurrencyDriver = cu.vName WHERE iDriverId = '" . $iMemberId . "'";
        $driverData = $obj->MySQLSelect($sqld);
        $currencycode = $driverData[0]['vCurrencyDriver'];
        $currencySymbol = $driverData[0]['vSymbol'];
        $priceRatio = $driverData[0]['Ratio'];
    }

    if ($currencycode == "" || $currencycode == null) {
        $sql = "SELECT vName,vSymbol,Ratio from currency WHERE eDefault = 'Yes'";
        $currencyData = $obj->MySQLSelect($sql);
        $currencycode = $currencyData[0]['vName'];
        $currencySymbol = $currencyData[0]['vSymbol'];
        $priceRatio = $currencyData[0]['Ratio'];
    }

    ######### Checking For Flattrip #########
    $isDestinationAdded = "No";
    if ($DestLatitude != "" && $DestLongitude != "") {
        $isDestinationAdded = "Yes";
    }
    if ($isDestinationAdded == "Yes") {
        $sourceLocationArr = array($PickUpLatitude, $PickUpLongitude);
        $destinationLocationArr = array($DestLatitude, $DestLongitude);
        $data_flattrip = checkFlatTripnew($sourceLocationArr, $destinationLocationArr, $selectedCarTypeID, $iRentalPackageId);
        $eFlatTrip = $data_flattrip['eFlatTrip'];
        $fFlatTripPrice = $data_flattrip['Flatfare'];
    } else {
        $eFlatTrip = "No";
        $fFlatTripPrice = 0;
    }
    ######### Checking For Flattrip #########

    if ($selectedTime != '' && $vTimeZone != '') {
        $systemTimeZone = date_default_timezone_get();
        $selectedTime = converToTz($selectedTime, $systemTimeZone, $vTimeZone);
    }

    //$APP_TYPE = $generalobj->getConfigurations("configurations","APP_TYPE");
    $SurgePriceValue = 1;
    if ($APP_TYPE == "UberX") {
        $data['Action'] = "1";
    } else {
        /*changed for rental*/
        $data = checkSurgePrice($selectedCarTypeID, $selectedTime, $iRentalPackageId);
        if ($data['Action'] == "0") {
            $SurgePriceValue = $data['SurgePriceValue'];
        }
    }

    if ($APPLY_SURGE_ON_FLAT_FARE == "No" && $eFlatTrip == "Yes") {
        $SurgePriceValue = 1;
        $data['Action'] = "1";
    }

    $fFlatTripPrice = round($fFlatTripPrice * $priceRatio, 2);
    $fSurgePriceDiff = round(($fFlatTripPrice * $SurgePriceValue) - $fFlatTripPrice, 2);
    $fFlatTripPrice = $fFlatTripPrice + $fSurgePriceDiff;
    $data['eFlatTrip'] = $eFlatTrip;
    $data['fFlatTripPrice'] = $fFlatTripPrice;
    $data['fFlatTripPricewithsymbol'] = $currencySymbol . " " . $fFlatTripPrice;

    echo json_encode($data);

?>