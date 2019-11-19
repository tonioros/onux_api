<?php 
    global $generalobj, $tconfig;
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $eUserType = isset($_REQUEST["eUserType"]) ? $_REQUEST["eUserType"] : 'Passenger';
    $iCabBookingId = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : '';
    //$APP_TYPE = $generalobj->getConfigurations("configurations","APP_TYPE");
    //$APP_TYPE = "UberX";
    if ($iCabBookingId != "") {
        $sql = "SELECT * from cab_booking WHERE iCabBookingId = '" . $iCabBookingId . "'";
        $bookingData = $obj->MySQLSelect($sql);
        if ($eUserType == "Passenger") {
            $tableName = "register_driver";
            $fields = 'iDriverId, vPhone,vCode as vPhoneCode, vEmail, CONCAT(vName," ",vLastName) as vName,vAvgRating,vImage as Imgname,vLang';
            $condfield = 'iDriverId';
            $UserId = $bookingData[0]['iDriverId'];
            $Photo_Gallery_folder_path = $tconfig['tsite_upload_images_driver_path'] . "/" . $UserId . "/";
            $Photo_Gallery_folder = $tconfig['tsite_upload_images_driver'] . "/" . $UserId . "/";
            $vCurrency = get_value('register_user', 'vCurrencyPassenger', 'iUserId', $bookingData[0]['iUserId'], '', 'true');
        } else {
            $tableName = "register_user";
            $fields = 'iUserId, vPhone,vPhoneCode as vPhoneCode, vEmail, CONCAT(vName," ",vLastName) as vName,vAvgRating,vImgName as Imgname,vLang';
            $condfield = 'iUserId';
            $UserId = $bookingData[0]['iUserId'];
            $Photo_Gallery_folder_path = $tconfig['tsite_upload_images_passenger_path'] . "/" . $UserId . "/";
            $Photo_Gallery_folder = $tconfig['tsite_upload_images_passenger'] . "/" . $UserId . "/";
            $vCurrency = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $bookingData[0]['iDriverId'], '', 'true');
        }
        $sql = "select $fields from $tableName where $condfield = '" . $UserId . "'";
        $db_member = $obj->MySQLSelect($sql);
        $lang = $db_member[0]['vLang'];
        if ($lang == "" || $lang == null) {
            $lang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        $db_member[0]['vLang'] = $lang;
        if ($vCurrency == "" || $vCurrency == null) {
            $vCurrency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
        }
        $UserCurrencyData = get_value('currency', 'vSymbol, Ratio', 'vName', $vCurrency);
        $priceRatio = $UserCurrencyData[0]['Ratio'];
        $vSymbol = $UserCurrencyData[0]['vSymbol'];
        $db_member[0]['vSymbol'] = $vSymbol;
        $imgpath = $Photo_Gallery_folder_path . "2_" . $db_member[0]['Imgname'];
        if ($db_member[0]['Imgname'] != "" && file_exists($imgpath)) {
            $db_member[0]['Imgname'] = $Photo_Gallery_folder . "2_" . $db_member[0]['Imgname'];
        } else {
            $db_member[0]['Imgname'] = "";
        }
        $vehicleDetailsArr = array();
        $iVehicleTypeId = $bookingData[0]['iVehicleTypeId'];
        $sql2 = "SELECT vc.iVehicleCategoryId, vc.iParentId,vc.vCategory_" . $lang . " as vCategory, vc.vCategoryTitle_" . $lang . " as vCategoryTitle, vc.tCategoryDesc_" . $lang . " as tCategoryDesc, vc.ePriceType, vt.vVehicleType_" . $lang . " as vVehicleType, vt.eFareType, vt.fFixedFare, vt.fPricePerHour, vt.fPricePerKM, vt.fPricePerMin, vt.iBaseFare,vt.fCommision, vt.iMinFare,vt.iPersonSize, vt.vLogo as vVehicleTypeImage, vt.eType, vt.eIconType, vt.eAllowQty, vt.iMaxQty, vt.iVehicleTypeId, fFixedFare FROM vehicle_category as vc LEFT JOIN vehicle_type AS vt ON vt.iVehicleCategoryId = vc.iVehicleCategoryId WHERE vt.iVehicleTypeId='" . $iVehicleTypeId . "'";
        $Data = $obj->MySQLSelect($sql2);
        $iParentId = $Data[0]['iParentId'];
        if ($iParentId == 0) {
            $ePriceType = $Data[0]['ePriceType'];
        } else {
            $ePriceType = get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
        }
        $ALLOW_SERVICE_PROVIDER_AMOUNT = $ePriceType == "Provider" ? "Yes" : "No";
        if ($Data[0]['eFareType'] == "Fixed") {
            //$fAmount = $vCurrencySymbol.$vehicleTypeData[0]['fFixedFare'];
            $fAmount = $Data[0]['fFixedFare'];
        } else if ($Data[0]['eFareType'] == "Hourly") {
            //$fAmount = $vCurrencySymbol.$vehicleTypeData[0]['fPricePerHour']."/hour";
            $fAmount = $Data[0]['fPricePerHour'];
        } else {
            $vDistance = $bookingData[0]['vDistance'];
            $vDuration = $bookingData[0]['vDuration'];
            $Minute_Fare = round($Data[0]['fPricePerMin'] * $vDuration, 2);
            $Distance_Fare = round($Data[0]['fPricePerKM'] * $vDistance, 2);
            $iBaseFare = round($Data[0]['iBaseFare'], 2);
            $fAmount = $iBaseFare + $Minute_Fare + $Distance_Fare;
        }
        $iPrice = $fAmount;
        if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes") {
            $sqlServicePro = "SELECT * FROM `service_pro_amount` WHERE iDriverVehicleId='" . $iDriverVehicleId . "' AND iVehicleTypeId='" . $iVehicleTypeId . "'";
            $serviceProData = $obj->MySQLSelect($sqlServicePro);
            if (count($serviceProData) > 0) {
                $fAmount = $serviceProData[0]['fAmount'];
            } else {
                $fAmount = $iPrice;
            }
            $iPrice = $fAmount;
        }
        $iPrice = $iPrice * $priceRatio;
        $iPrice = round($iPrice, 2);
        $vehicleDetailsArr['fAmount'] = $vSymbol . " " . $iPrice;
        $vehicleDetailsArr['ePriceType'] = $ePriceType;
        $vehicleDetailsArr['ALLOW_SERVICE_PROVIDER_AMOUNT'] = $ALLOW_SERVICE_PROVIDER_AMOUNT;
        $returnArr['Action'] = "1";
        $returnArr['MemberDetails'] = $db_member;
        $returnArr['VehicleDetails'] = $vehicleDetailsArr;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);exit;

?>