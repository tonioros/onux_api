<?php 
    global $generalobj;

    $iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? clean($_REQUEST['iVehicleCategoryId']) : 0;
    $vLatitude = isset($_REQUEST["vLatitude"]) ? $_REQUEST["vLatitude"] : '';
    $vLongitude = isset($_REQUEST["vLongitude"]) ? $_REQUEST["vLongitude"] : '';
    $userId = isset($_REQUEST['userId']) ? clean($_REQUEST['userId']) : '';
    $eCheck = isset($_REQUEST['eCheck']) ? clean($_REQUEST['eCheck']) : 'No';
    $pickuplocationarr = array($vLatitude, $vLongitude);
    $GetVehicleIdfromGeoLocation = GetVehicleTypeFromGeoLocation($pickuplocationarr);
    if ($eCheck == "" || $eCheck == null) {
        $eCheck = "No";
    }
    if ($eCheck == "Yes") {
        //$allowed_ans = checkRestrictedAreaNew($pickuplocationarr,"No");
        $allowed_ans = checkAllowedAreaNew($pickuplocationarr, "No");
        if ($allowed_ans == "Yes") {
            $sql23 = "SELECT iVehicleTypeId FROM `vehicle_type` WHERE iLocationid IN ($GetVehicleIdfromGeoLocation) AND iVehicleCategoryId = '" . $iVehicleCategoryId . "' ORDER BY iDisplayOrder ASC";
            $vehicleTypes = $obj->MySQLSelect($sql23);
            if (count($vehicleTypes) > 0) {
                $returnArr['Action'] = "1";
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_NO_SERVICES_AVAIL_FOR_JOB_LOC";
            }
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_JOB_LOCATION_NOT_ALLOWED";
        }
        $obj->MySQLClose();
        echo json_encode($returnArr);exit;
    } else {
        if ($userId != "") {
            $sql1 = "SELECT vLang,vCurrencyPassenger FROM `register_user` WHERE iUserId='$userId'";
            $row = $obj->MySQLSelect($sql1);
            $lang = $row[0]['vLang'];
            if ($lang == "" || $lang == null) {$lang = "EN";}

            $vCurrencyPassenger = $row[0]['vCurrencyPassenger'];
            if ($vCurrencyPassenger == "" || $vCurrencyPassenger == null) {
                $vCurrencyPassenger = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
            }
            $UserCurrencyData = get_value('currency', 'vSymbol, Ratio', 'vName', $vCurrencyPassenger);
            $priceRatio = $UserCurrencyData[0]['Ratio'];
            $vSymbol = $UserCurrencyData[0]['vSymbol'];

            $vehicleCategoryData = get_value('vehicle_category', "vCategoryTitle_" . $lang . " as vCategoryTitle, tCategoryDesc_" . $lang . " as tCategoryDesc", 'iVehicleCategoryId', $iVehicleCategoryId);
            $vCategoryTitle = $vehicleCategoryData[0]['vCategoryTitle'];
            $vCategoryDesc = $vehicleCategoryData[0]['tCategoryDesc'];
            $sql2 = "SELECT vc.iVehicleCategoryId, vc.iParentId,vc.vCategory_" . $lang . " as vCategory, vc.ePriceType, vt.iVehicleTypeId, vt.vVehicleType_" . $lang . " as vVehicleType, vt.eFareType, vt.fFixedFare, vt.fPricePerHour, vt.fPricePerKM, vt.fPricePerMin, vt.iBaseFare,vt.fCommision, vt.iMinFare,vt.iPersonSize, vt.vLogo as vVehicleTypeImage, vt.eType, vt.eIconType, vt.eAllowQty, vt.iMaxQty, vt.iVehicleTypeId, fFixedFare FROM vehicle_category as vc LEFT JOIN vehicle_type AS vt ON vt.iVehicleCategoryId = vc.iVehicleCategoryId WHERE vc.eStatus='Active' AND vt.iVehicleCategoryId='$iVehicleCategoryId' AND vt.iLocationid IN ($GetVehicleIdfromGeoLocation) ORDER BY vt.iDisplayOrder ASC";
            //AND vt.eType='UberX'

            $Data = $obj->MySQLSelect($sql2);
            if (!empty($Data)) {
                for ($i = 0; $i < count($Data); $i++) {
                    $Data[$i]['fFixedFare_value'] = round($Data[$i]['fFixedFare'] * $priceRatio, 2);
                    $fFixedFare = round($Data[$i]['fFixedFare'] * $priceRatio, 2);
                    $Data[$i]['fFixedFare'] = $vSymbol . formatNum($fFixedFare);
                    $Data[$i]['fPricePerHour_value'] = round($Data[$i]['fPricePerHour'] * $priceRatio, 2);
                    $fPricePerHour = round($Data[$i]['fPricePerHour'] * $priceRatio, 2);
                    $Data[$i]['fPricePerHour'] = $vSymbol . formatNum($fPricePerHour);
                    $Data[$i]['fPricePerKM'] = getVehicleCountryUnit_PricePerKm($Data[$i]['iVehicleTypeId'], $Data[$i]['fPricePerKM'], $userId, "Passenger");
                    $fPricePerKM = round($Data[$i]['fPricePerKM'] * $priceRatio, 2);
                    $Data[$i]['fPricePerKM'] = $vSymbol . formatNum($fPricePerKM);
                    $fPricePerMin = round($Data[$i]['fPricePerMin'] * $priceRatio, 2);
                    $Data[$i]['fPricePerMin'] = $vSymbol . formatNum($fPricePerMin);
                    $iBaseFare = round($Data[$i]['iBaseFare'] * $priceRatio, 2);
                    $Data[$i]['iBaseFare'] = $vSymbol . formatNum($iBaseFare);
                    $fCommision = round($Data[$i]['fCommision'] * $priceRatio, 2);
                    $Data[$i]['fCommision'] = $vSymbol . formatNum($fCommision);
                    $iMinFare = round($Data[$i]['iMinFare'] * $priceRatio, 2);
                    $Data[$i]['iMinFare'] = $vSymbol . formatNum($iMinFare);
                    $Data[$i]['vSymbol'] = $vSymbol;
                    $Data[$i]['vCategoryTitle'] = $vCategoryTitle;
                    $Data[$i]['vCategoryDesc'] = $vCategoryDesc;
                    $iParentId = $Data[$i]['iParentId'];
                    if ($iParentId == 0) {
                        $ePriceType = $Data[$i]['ePriceType'];
                    } else {
                        $ePriceType = get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
                    }
                    $Data[$i]['ePriceType'] = $ePriceType;
                    $Data[$i]['ALLOW_SERVICE_PROVIDER_AMOUNT'] = $ePriceType == "Provider" ? "Yes" : "No";
                    //$Data[$i]['ALLOW_SERVICE_PROVIDER_AMOUNT']= $Data[$i]['ePriceType'] == "Provider"? "Yes" :"No";
                }

                $returnArr['Action'] = "1";
                $returnArr['message'] = $Data;
                //$returnArr['ALLOW_SERVICE_PROVIDER_AMOUNT'] = $ALLOW_SERVICE_PROVIDER_AMOUNT;
                $returnArr['vCategoryTitle'] = $vCategoryTitle;
                $returnArr['vCategoryDesc'] = $vCategoryDesc;
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_NO_DATA_AVAIL";
            }
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }
    }
    echo json_encode($returnArr, JSON_HEX_QUOT | JSON_HEX_TAG);
?>