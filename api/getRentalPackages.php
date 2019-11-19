<?php 
    global $generalobj, $obj;
    $iVehicleTypeId = isset($_REQUEST["iVehicleTypeId"]) ? $_REQUEST["iVehicleTypeId"] : '';
    $GeneralMemberId = isset($_REQUEST["GeneralMemberId"]) ? $_REQUEST["GeneralMemberId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
    $couponCode = isset($_REQUEST["PromoCode"]) ? $_REQUEST["PromoCode"] : '';
    if ($UserType == 'Passenger') {
        $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol,cu.Ratio FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE ru.iUserId = '" . $GeneralMemberId . "'";
        $passengerData = $obj->MySQLSelect($sqlp);
        $vLang = $passengerData[0]['vLang'];
        $vCurrency = $passengerData[0]['vCurrencyPassenger'];
        $vCurrencySymbol = $passengerData[0]['vSymbol'];
        $priceRatio = $passengerData[0]['Ratio'];
    } else {
        $sqlp = "SELECT rd.vCurrencyDriver,rd.vLang,cu.vSymbol,cu.Ratio FROM register_driver as rd LEFT JOIN currency as cu ON rd.vCurrencyDriver = cu.vName WHERE rd.iDriverId = '" . $GeneralMemberId . "'";
        $DriverData = $obj->MySQLSelect($sqlp);
        $vLang = $DriverData[0]['vLang'];
        $vCurrency = $DriverData[0]['vCurrencyDriver'];
        $vCurrencySymbol = $DriverData[0]['vSymbol'];
        $priceRatio = $DriverData[0]['Ratio'];
    }
    if ($vLang == "" || $vLang == null) {
        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $sql = "SELECT iRentalPackageId,vPackageName_" . $vLang . " as vPackageName,fPrice,fKiloMeter,fHour,fPricePerKM,fPricePerHour FROM `rental_package` WHERE iVehicleTypeId = '$iVehicleTypeId' ORDER BY `fPrice` ASC ";
    $RentalPackagesData = $obj->MySQLSelect($sql);
    $totalcount = count($RentalPackagesData);
    for ($i = 0; $i < count($RentalPackagesData); $i++) {
        $fKiloMeter = getRentalKilometer_ByCountry($GeneralMemberId, $UserType, $iVehicleTypeId, $RentalPackagesData[$i]['fKiloMeter']);
        $RentalPackagesData[$i]['fKiloMeter'] = round($fKiloMeter, 2);
        $fPricePerKM = getRentalPrice_ByCountry($GeneralMemberId, $UserType, $iVehicleTypeId, $RentalPackagesData[$i]['fPricePerKM']);
        $RentalPackagesData[$i]['fPricePerKM'] = $vCurrencySymbol . formatNum($fPricePerKM * $priceRatio);
        $RentalPackagesData[$i]['fPricePerHour'] = $vCurrencySymbol . formatNum($RentalPackagesData[$i]['fPricePerHour'] * $priceRatio);
        //$RentalPackagesData[$i]['fPrice']= $vCurrencySymbol.formatNum($RentalPackagesData[$i]['fPrice'] * $priceRatio);
        $fPrice = $RentalPackagesData[$i]['fPrice'];
        ### Checking Promocode Discount ##
        $discountValue = 0;
        $discountValueType = "cash";
        if ($couponCode != "") {
            $discountValue = get_value('coupon', 'fDiscount', 'vCouponCode', $couponCode, '', 'true');
            $discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode, '', 'true');
            if ($discountValueType == "percentage") {
                $vDiscount = round($discountValue, 1) . ' ' . "%";
                $discountValue = round(($fPrice * $discountValue), 1) / 100;
            } else {
                $curr_sym = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
                if ($discountValue > $fPrice) {
                    $vDiscount = round($fPrice, 1) . ' ' . $curr_sym;
                } else {
                    $vDiscount = round($discountValue, 1) . ' ' . $curr_sym;
                }
            }
            $fPrice = $fPrice - $discountValue;
            if ($fPrice < 0) {
                $fPrice = 0;
            }
        }
        $RentalPackagesData[$i]['fPrice'] = $vCurrencySymbol . formatNum($fPrice * $priceRatio);
        ### Checking Promocode Discount ##
    }
    if ($totalcount > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $RentalPackagesData;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    $get_make = "SELECT m.vMake,mo.vTitle FROM driver_vehicle as dv LEFT JOIN make as m on m.iMakeId=dv.iMakeId LEFT JOIN model as mo on mo.iModelId=dv.iModelId WHERE dv.iMakeId > 0 AND FIND_IN_SET ('" . $iVehicleTypeId . "', dv.vRentalCarType) GROUP BY m.vMake LIMIT 0,3";
    $makemodaldata = $obj->MySQLSelect($get_make);
    $s = array();
    if (!empty($makemodaldata)) {
        foreach ($makemodaldata as $key => $value) {
            $s[] = $value['vMake'] . $value['vTitle'];
        }
        $returnArr['vehicle_list_title'] = implode(', ', $s);
    } else {
        $returnArr['vehicle_list_title'] = '';
    }
    $pageDesc = get_value('pages', 'tPageDesc_' . $vLang, 'iPageId', '46', '', 'true');
    $returnArr['page_desc'] = $pageDesc;
    echo json_encode($returnArr, JSON_UNESCAPED_UNICODE);exit;

?>