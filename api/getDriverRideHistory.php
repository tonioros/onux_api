<?php 
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $date = isset($_REQUEST["date"]) ? $_REQUEST["date"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $date = $date . " " . "12:01:00";
    $date = date("Y-m-d H:i:s", strtotime($date));
    $serverTimeZone = date_default_timezone_get();
    $date = converToTz($date, $serverTimeZone, $vTimeZone, "Y-m-d");

    /*$vCurrencyDriver=get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $iDriverId,'','true');
    $vLanguage=get_value('register_driver', 'vLang', 'iDriverId',$iDriverId,'','true');*/
    $DriverDetail = get_value('register_driver', 'vCurrencyDriver,vLang', 'iDriverId', $iDriverId);
    $vCurrencyDriver = $DriverDetail[0]['vCurrencyDriver'];
    $vLanguage = $DriverDetail[0]['vLang'];
    // $currencySymbol=get_value('currency', 'vSymbol', 'eDefault', 'Yes','','true');
    // $priceRatio=1;
    // $fRatioDriver = get_value('currency', 'Ratio', 'vName', $vCurrencyDriver,'','true');
    $currencySymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyDriver, '', 'true');

    if ($vLanguage == "" || $vLanguage == null) {
        $vLanguage = "EN";
    }

    //$sql = "SELECT tr.*, rate.vRating1, rate.vMessage,ru.vName,ru.vLastName,ru.vImgName as vImage FROM trips as tr,ratings_user_driver as rate,register_user as ru WHERE tr.iDriverId='$iDriverId' AND tr.tTripRequestDate LIKE '".$date."%' AND tr.iActive='Finished' AND rate.iTripId = tr.iTripId AND rate.eUserType='Passenger' AND ru.iUserId=tr.iUserId";
    $sql = "SELECT tr.*, ru.vName,ru.vLastName,ru.vImgName as vImage FROM trips as tr,register_user as ru WHERE tr.iDriverId='$iDriverId' AND tr.tTripRequestDate LIKE '" . $date . "%' AND ( tr.iActive='Finished' OR (tr.iActive='Canceled' AND (tr.fCancellationFare > 0 OR tr.fWalletDebit > 0)))   AND ru.iUserId=tr.iUserId ORDER By tr.iTripId DESC";
    $tripData = $obj->MySQLSelect($sql);

    $totalEarnings = 0;
    $avgRating = 0;

    if (count($tripData) > 0) {

        for ($i = 0; $i < count($tripData); $i++) {
            /*added for rental*/
            if ($tripData[$i]['iRentalPackageId'] > 0) {
                $tripData[$i]['eRental'] = "Yes";
                /*$rentalData = getRentalData($tripData[$i]['iRentalPackageId']);
            $tripData[$i]['vPackageName'] = $rentalData[0]['vPackageName_'.$vLanguage];*/
            } else {
                $tripData[$i]['eRental'] = "";
                //$tripData[$i]['vPackageName'] = "";
            }
            /*End added for rental*/
            // $iFare = $tripData[$i]['fTripGenerateFare']-$tripData[$i]['fTollPrice'];
            $iActive = $tripData[$i]['iActive'];
            if ($iActive == "Finished") {
                $iFare = $tripData[$i]['fTripGenerateFare'];
            } else {
                $iFare = $tripData[$i]['fCancellationFare'] + $tripData[$i]['fWalletDebit'];
            }

            //$iFare = $tripData[$i]['fTripGenerateFare'];
            $fCommision = $tripData[$i]['fCommision'];
            $fDiscount = $tripData[$i]['fDiscount'];
            $fTipPrice = $tripData[$i]['fTipPrice'];
            $fTollPrice = $tripData[$i]['fTollPrice'];
            $fTax1 = $tripData[$i]['fTax1'];
            $fTax2 = $tripData[$i]['fTax2'];
            $fOutStandingAmount = $tripData[$i]['fOutStandingAmount'];
            //$vRating1 = $tripData[$i]['vRating1'];
            $priceRatio = $tripData[$i]['fRatio_' . $vCurrencyDriver];

            $sql = "SELECT vRating1, vMessage FROM ratings_user_driver WHERE iTripId = '" . $tripData[$i]['iTripId'] . "' AND eUserType='Passenger'";
            $tripData_rating = $obj->MySQLSelect($sql);
            if (count($tripData_rating) > 0) {
                $tripData[$i]['vRating1'] = $tripData_rating[0]['vRating1'];
                $tripData[$i]['vMessage'] = $tripData_rating[0]['vMessage'];
                $vRating1 = $tripData_rating[0]['vRating1'];
            } else {
                $tripData[$i]['vRating1'] = "0";
                $tripData[$i]['vMessage'] = "";
                $vRating1 = 0;
            }

            if (($iFare == "" || $iFare == 0) && $fDiscount > 0) {
                $incValue = ($fDiscount - $fCommision - $fTax1 - $fTax2 - $fOutStandingAmount) + $fTipPrice;
                $totalEarnings = $totalEarnings + ($incValue * $priceRatio);
            } else if ($iFare != "" && $iFare > 0) {
                $incValue = ($iFare - $fCommision - $fTax1 - $fTax2 - $fOutStandingAmount) + $fTipPrice;
                $totalEarnings = $totalEarnings + ($incValue * $priceRatio);
            }

            $avgRating = $avgRating + $vRating1;

            $returnArr = getTripPriceDetails($tripData[$i]['iTripId'], $iDriverId, "Driver");
            $tripData[$i] = array_merge($tripData[$i], $returnArr);

            $eType = $tripData[$i]['eType'];
            $iVehicleTypeId = $tripData[$i]['iVehicleTypeId'];
            $eFareType = get_value('vehicle_type', 'eFareType', 'iVehicleTypeId', $iVehicleTypeId, '', 'true');
            if ($eType == 'UberX' && $eFareType != "Regular") {
                $tripData[$i]['tDaddress'] = "";
            }
        }

        $returnArr['Action'] = "1";
        $returnArr['message'] = $tripData;

        ## Checking For Cancel Trip ##
        $sqlc = "SELECT tr.*, ru.vName,ru.vLastName,ru.vImgName as vImage FROM trips as tr,register_user as ru WHERE tr.iDriverId='$iDriverId' AND tr.tTripRequestDate LIKE '" . $date . "%' AND tr.iActive='Canceled' AND ru.iUserId=tr.iUserId ORDER By tr.iTripId DESC";
        $tripcancelData = $obj->MySQLSelect($sqlc);
        if (count($tripcancelData) > 0) {
            for ($j = 0; $j < count($tripcancelData); $j++) {
                $returnArr_cancel = getTripPriceDetails($tripcancelData[$j]['iTripId'], $iDriverId, "Driver");
                $tripcancelData[$j] = array_merge($tripcancelData[$j], $returnArr_cancel);
            }
            //echo "<pre>";print_r($tripcancelData);exit;
            $returnArr['message1'] = $tripcancelData;
        }
        ## Checking For Cancel Trip ##

    } else {
        ## Checking For Cancel Trip ##
        $sqlc = "SELECT tr.*, ru.vName,ru.vLastName,ru.vImgName as vImage FROM trips as tr,register_user as ru WHERE tr.iDriverId='$iDriverId' AND tr.tTripRequestDate LIKE '" . $date . "%' AND tr.iActive='Canceled' AND ru.iUserId=tr.iUserId ORDER By tr.iTripId DESC";
        $tripcancelData = $obj->MySQLSelect($sqlc);
        if (count($tripcancelData) > 0) {
            for ($j = 0; $j < count($tripcancelData); $j++) {
                $returnArr_cancel = getTripPriceDetails($tripcancelData[$j]['iTripId'], $iDriverId, "Driver");
                $tripcancelData[$j] = array_merge($tripcancelData[$j], $returnArr_cancel);
            }
            //echo "<pre>";print_r($tripcancelData);exit;
            $returnArr['message1'] = $tripcancelData;
            $returnArr['Action'] = "1";
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_NO_DATA_AVAIL";
        }
        ## Checking For Cancel Trip ##
    }
    //$returnArr['TotalEarning'] = strval(round($totalEarnings,2));
    $returnArr['TotalEarning'] = strval(formatnum($totalEarnings));
    $returnArr['TripDate'] = date('l, dS M Y', strtotime($date));
    $returnArr['TripCount'] = strval(count($tripData));
    //$returnArr['AvgRating'] = strval(round(count($tripData) == 0? 0 : ($avgRating/count($tripData)),2));
    $returnArr['AvgRating'] = strval(getMemberAverageRating($iDriverId, "Driver", $date));
    $returnArr['CurrencySymbol'] = $currencySymbol;

    echo json_encode($returnArr);

?>