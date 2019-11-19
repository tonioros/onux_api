<?php 
    global $generalobj;

    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $bookingType = isset($_REQUEST["bookingType"]) ? $_REQUEST["bookingType"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $dataType = isset($_REQUEST["DataType"]) ? $_REQUEST["DataType"] : '';
    //$APP_TYPE = $generalobj->getConfigurations("configurations", "APP_TYPE");

    $per_page = 10;
    $additional_mins = $BOOKING_LATER_ACCEPT_AFTER_INTERVAL;
    $currDate = date('Y-m-d H:i:s');
    $currDate = date("Y-m-d H:i:s", strtotime($currDate . "-" . $additional_mins . " minutes"));
    $ssql1 = " AND dBooking_date > '" . $currDate . "'";
    $ssql2 = " AND cb.dBooking_date > '" . $currDate . "'";

    if ($UserType == "Driver") {
        /*if($APP_TYPE == "UberX"){
        if($dataType == "PENDING"){
        $sql_all  = "SELECT COUNT(iCabBookingId) As TotalIds FROM cab_booking WHERE iDriverId != '' AND eStatus = 'Pending' AND iDriverId='".$iDriverId."'".$ssql1;
        }else{
        $sql_all  = "SELECT COUNT(iCabBookingId) As TotalIds FROM cab_booking WHERE iDriverId != '' AND eStatus = 'Accepted' AND iDriverId='".$iDriverId."'". $ssql1;
        }
        }else{
        $sql_all  = "SELECT COUNT(iCabBookingId) As TotalIds FROM cab_booking WHERE iDriverId != '' AND eStatus = 'Assign' AND iDriverId='".$iDriverId."'".$ssql1;
        }    */
        if ($dataType == "PENDING") {
            $sql_all = "SELECT COUNT(iCabBookingId) As TotalIds FROM cab_booking WHERE iDriverId != '' AND eStatus = 'Pending' AND iDriverId='" . $iDriverId . "'" . $ssql1;
        } else {
            $sql_all = "SELECT COUNT(iCabBookingId) As TotalIds FROM cab_booking WHERE iDriverId != '' AND ( eStatus = 'Accepted' || eStatus = 'Assign' ) AND iDriverId='" . $iDriverId . "'" . $ssql1;
        }

    } else {
        $sql_all = "SELECT COUNT(iCabBookingId) As TotalIds FROM cab_booking WHERE  iUserId='$iUserId' AND  ( eStatus = 'Assign' OR eStatus = 'Pending' OR eStatus = 'Accepted' OR eStatus = 'Declined' OR eStatus = 'Cancel') AND eCancelBy != 'Rider' $ssql1";
    }

    $data_count_all = $obj->MySQLSelect($sql_all);
    $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);

    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;

    if ($UserType == "Driver") {
        /*if($APP_TYPE == "UberX"){
        if($dataType == "PENDING"){
        $sql = "SELECT cb.* FROM `cab_booking` as cb  WHERE cb.iDriverId != '' AND  cb.eStatus = 'Pending' AND cb.iDriverId='$iDriverId' $ssql2 ORDER BY cb.iCabBookingId DESC" . $limit;
        }else{
        $sql = "SELECT cb.* FROM `cab_booking` as cb  WHERE cb.iDriverId != '' AND  cb.eStatus = 'Accepted' AND cb.iDriverId='$iDriverId' $ssql2 ORDER BY cb.iCabBookingId DESC" . $limit;
        }
        }else{
        $sql = "SELECT cb.* FROM `cab_booking` as cb  WHERE cb.iDriverId != '' AND  cb.eStatus = 'Assign' AND cb.iDriverId='$iDriverId' $ssql2 ORDER BY cb.iCabBookingId DESC" . $limit;
        } */
        if ($dataType == "PENDING") {
            $sql = "SELECT cb.* FROM `cab_booking` as cb  WHERE cb.iDriverId != '' AND  cb.eStatus = 'Pending' AND cb.iDriverId='$iDriverId' $ssql2 ORDER BY cb.iCabBookingId DESC" . $limit;
        } else {
            $sql = "SELECT cb.* FROM `cab_booking` as cb  WHERE cb.iDriverId != '' AND  ( cb.eStatus = 'Accepted' || cb.eStatus = 'Assign' )  AND cb.iDriverId='$iDriverId' $ssql2 ORDER BY cb.iCabBookingId DESC" . $limit;
        }
    } else {
        $sql = "SELECT cb.* FROM `cab_booking` as cb  WHERE cb.iUserId='$iUserId' AND ( cb.eStatus = 'Assign' OR cb.eStatus = 'Pending' OR eStatus = 'Accepted' OR eStatus = 'Declined'  OR eStatus = 'Cancel' ) AND eCancelBy != 'Rider' $ssql2 ORDER BY cb.iCabBookingId DESC" . $limit;
    }
    $Data = $obj->MySQLSelect($sql);
    $totalNum = count($Data);

    if (count($Data) > 0) {

        for ($i = 0; $i < count($Data); $i++) {
            $Data[$i]['dBooking_dateOrig'] = $Data[$i]['dBooking_date'];
            // Convert Into Timezone
            $tripTimeZone = $Data[0]['vTimeZone'];
            if ($tripTimeZone != "") {
                $serverTimeZone = date_default_timezone_get();
                $Data[$i]['dBooking_dateOrig'] = converToTz($Data[$i]['dBooking_dateOrig'], $tripTimeZone, $serverTimeZone);
            }
            // Convert Into Timezone
            $Data[$i]['dBooking_date'] = date('dS M Y \a\t h:i a', strtotime($Data[$i]['dBooking_date']));

            $eType = $Data[$i]['eType'];
            $iVehicleTypeId = $Data[$i]['iVehicleTypeId'];
            $eFareType = get_value('vehicle_type', 'eFareType', 'iVehicleTypeId', $iVehicleTypeId, '', 'true');
            $Data[$i]['eFareType'] = $eFareType;
            if ($eType == 'UberX') {
                $DisplayBookingDetails = array();
                $DisplayBookingDetails = DisplayBookingDetails($Data[$i]['iCabBookingId']);
                $Data[$i]['tDestAddress'] = "";
                $Data[$i]['selectedtime'] = $DisplayBookingDetails['selectedtime'];
                $Data[$i]['selecteddatetime'] = $DisplayBookingDetails['selecteddatetime'];
                $Data[$i]['SelectedFareType'] = $DisplayBookingDetails['SelectedFareType'];
                $Data[$i]['SelectedQty'] = $DisplayBookingDetails['SelectedQty'];
                $Data[$i]['SelectedPrice'] = strval($DisplayBookingDetails['SelectedPrice']);
                $Data[$i]['SelectedCurrencySymbol'] = $DisplayBookingDetails['SelectedCurrencySymbol'];
                $Data[$i]['SelectedCurrencyRatio'] = $DisplayBookingDetails['SelectedCurrencyRatio'];
                $Data[$i]['SelectedVehicle'] = $DisplayBookingDetails['SelectedVehicle'];
                $Data[$i]['SelectedCategory'] = $DisplayBookingDetails['SelectedCategory'];
                $Data[$i]['vVehicleType'] = $DisplayBookingDetails['SelectedVehicle'];
                $Data[$i]['vVehicleCategory'] = $DisplayBookingDetails['SelectedCategory'];
                $Data[$i]['SelectedCategoryId'] = $DisplayBookingDetails['SelectedCategoryId'];
                $Data[$i]['SelectedCategoryTitle'] = $DisplayBookingDetails['SelectedCategoryTitle'];
                $Data[$i]['SelectedCategoryDesc'] = $DisplayBookingDetails['SelectedCategoryDesc'];
                $Data[$i]['SelectedAllowQty'] = $DisplayBookingDetails['SelectedAllowQty'];
                $Data[$i]['SelectedPriceType'] = $DisplayBookingDetails['SelectedPriceType'];
                $Data[$i]['ALLOW_SERVICE_PROVIDER_AMOUNT'] = $DisplayBookingDetails['ALLOW_SERVICE_PROVIDER_AMOUNT'];
            } else {
                if ($UserType == "Passenger") {
                    $vLang = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
                } else {
                    $vLang = get_value('register_driver', 'vLang', 'iDriverId', $iDriverId, '', 'true');
                }
                if ($vLang == "" || $vLang == null) {
                    $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                }
                $vVehicleTypeName = get_value('vehicle_type', 'vVehicleType_' . $vLang, 'iVehicleTypeId', $iVehicleTypeId, '', 'true');
                $Data[$i]['vVehicleType'] = $vVehicleTypeName;
                $Data[$i]['vVehicleCategory'] = "";
            }
            /*added for rental*/
            if ($Data[$i]['iRentalPackageId'] > 0) {
                $rentalData = getRentalData($Data[$i]['iRentalPackageId']);
                $Data[$i]['vPackageName'] = $rentalData[0]['vPackageName_' . $vLang];
            } else {
                $Data[$i]['vPackageName'] = "";
            }
            /*end added for rental*/
        }
        $returnArr['Action'] = "1";
        $returnArr['message'] = $Data;

        if ($TotalPages > $page) {
            $returnArr['NextPage'] = $page + 1;
        } else {
            $returnArr['NextPage'] = "0";
        }

    } else {
        $returnArr['Action'] = "0";
        //$returnArr['message']= ($bookingType == "Ride" || $bookingType == "UberX")?"LBL_NO_BOOKINGS_AVAIL":"LBL_NO_DELIVERY_AVAIL";
        $returnArr['message'] = "LBL_NO_DATA_AVAIL";
    }

    echo json_encode($returnArr);

?>