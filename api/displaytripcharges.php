<?php 

    $TripID = isset($_REQUEST["TripID"]) ? $_REQUEST["TripID"] : '';
    $destination_lat = isset($_REQUEST["dest_lat"]) ? $_REQUEST["dest_lat"] : '';
    $destination_lon = isset($_REQUEST["dest_lon"]) ? $_REQUEST["dest_lon"] : '';
    $iTripTimeId = isset($_REQUEST["iTripTimeId"]) ? $_REQUEST["iTripTimeId"] : '';
    //$ALLOW_SERVICE_PROVIDER_AMOUNT = $generalobj->getConfigurations("configurations", "ALLOW_SERVICE_PROVIDER_AMOUNT");

    $where = " iTripId = '" . $TripID . "'";
    $data_update['tEndDate'] = @date("Y-m-d H:i:s");
    $data_update['tEndLat'] = $destination_lat;
    $data_update['tEndLong'] = $destination_lon;
    $obj->MySQLQueryPerform("trips", $data_update, 'update', $where);

    if ($iTripTimeId != "") {
        $where = " iTripTimeId = '$iTripTimeId'";
        $Data_update['dPauseTime'] = $data_update['tEndDate'];
        $Data_update['iTripId'] = $TripID;
        $id = $obj->MySQLQueryPerform("trip_times", $Data_update, 'update', $where);
    }

    $sql = "SELECT * from trips WHERE iTripId = '" . $TripID . "'";
    $tripData = $obj->MySQLSelect($sql);
    // echo "<pre>"; print_r($tripData); die;
    $iDriverVehicleId = $tripData[0]['iDriverVehicleId'];
    $iVehicleTypeId = $tripData[0]['iVehicleTypeId'];
    $fVisitFee = $tripData[0]['fVisitFee'];
    $startDate = $tripData[0]['tStartDate'];
    $endDateOfTrip = $tripData[0]['tEndDate'];
    $iQty = $tripData[0]['iQty'];
    //$endDateOfTrip=@date("Y-m-d H:i:s");
    /*$iVehicleCategoryId=get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId',$iVehicleTypeId,'','true');
    $iParentId = get_value('vehicle_category', 'iParentId', 'iVehicleCategoryId', $iVehicleCategoryId,'','true');*/
    $sql = "SELECT vc.iParentId from vehicle_category as vc LEFT JOIN vehicle_type as vt ON vc.iVehicleCategoryId=vt.iVehicleCategoryId WHERE vt.iVehicleTypeId = '" . $iVehicleTypeId . "'";
    $VehicleCategoryData = $obj->MySQLSelect($sql);
    $iParentId = $VehicleCategoryData[0]['iParentId'];
    if ($iParentId == 0) {
        $ePriceType = get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
    } else {
        $ePriceType = get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
    }
    //$ePriceType=get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId',$iVehicleCategoryId,'','true');
    $ALLOW_SERVICE_PROVIDER_AMOUNT = $ePriceType == "Provider" ? "Yes" : "No";

    if ($tripData[0]['eFareType'] == 'Hourly') {
        $sql22 = "SELECT * FROM `trip_times` WHERE iTripId='$TripID'";
        $db_tripTimes = $obj->MySQLSelect($sql22);

        $totalSec = 0;
        $iTripTimeId = '';
        foreach ($db_tripTimes as $dtT) {
            if ($dtT['dPauseTime'] != '' && $dtT['dPauseTime'] != '0000-00-00 00:00:00') {
                $totalSec += strtotime($dtT['dPauseTime']) - strtotime($dtT['dResumeTime']);
            }
        }
        $totalTimeInMinutes_trip = @round(abs($totalSec) / 60, 2);
    } else {
        $totalTimeInMinutes_trip = @round(abs(strtotime($startDate) - strtotime($endDateOfTrip)) / 60, 2);
    }
    $totalHour = $totalTimeInMinutes_trip / 60;
    $tripDistance = calcluateTripDistance($tripId);
    $sourcePointLatitude = $tripData[0]['tStartLat'];
    $sourcePointLongitude = $tripData[0]['tStartLong'];
    if ($totalTimeInMinutes_trip <= 1) {
        $FinalDistance = $tripDistance;
    } else {
        $FinalDistance = checkDistanceWithGoogleDirections($tripDistance, $sourcePointLatitude, $sourcePointLongitude, $destination_lat, $destination_lon);
    }
    $tripDistance = $FinalDistance;
    $fPickUpPrice = $tripData[0]['fPickUpPrice'];
    $fNightPrice = $tripData[0]['fNightPrice'];
    $eFareType = get_value('trips', 'eFareType', 'iTripId', $TripID, '', 'true');
    $surgePrice = $fPickUpPrice > 1 ? $fPickUpPrice : ($fNightPrice > 1 ? $fNightPrice : 1);
    $fAmount = 0;
    $Fare_data = getVehicleFareConfig("vehicle_type", $iVehicleTypeId);
    // echo "<pre>"; print_r($tripData); die;
    $fPricePerKM = getVehicleCountryUnit_PricePerKm($iVehicleTypeId, $Fare_data[0]['fPricePerKM']);
    /*$Minute_Fare = round($Fare_data[0]['fPricePerMin'] * $totalTimeInMinutes_trip * $surgePrice,2);
    $Distance_Fare = round($fPricePerKM * $tripDistance * $surgePrice,2);
    $iBaseFare = round($Fare_data[0]['iBaseFare'] * $surgePrice,2);
    $total_fare = $iBaseFare + $Minute_Fare + $Distance_Fare;*/
    $Minute_Fare = $Fare_data[0]['fPricePerMin'] * $totalTimeInMinutes_trip;
    $Distance_Fare = $fPricePerKM * $tripDistance;
    $iBaseFare = $Fare_data[0]['iBaseFare'];
    $total_fare = $iBaseFare + $Minute_Fare + $Distance_Fare;
    $fSurgePriceDiff = (($total_fare * $surgePrice) - $total_fare);
    $total_fare = $total_fare + $fSurgePriceDiff;

    $iMinFare = round($Fare_data[0]['iMinFare'] * $surgePrice, 2);
    if ($iMinFare > $total_fare) {
        $total_fare = $iMinFare;
    }
    if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes") {

        $sqlServicePro = "SELECT * FROM `service_pro_amount` WHERE iDriverVehicleId='" . $iDriverVehicleId . "' AND iVehicleTypeId='" . $iVehicleTypeId . "'";
        $serviceProData = $obj->MySQLSelect($sqlServicePro);

        if (count($serviceProData) > 0) {
            $fAmount = $serviceProData[0]['fAmount'];
            if ($eFareType == "Fixed") {
                $fAmount = $fAmount * $iQty;
            } else if ($eFareType == "Hourly") {
                $fAmount = $fAmount * $totalHour;
            } else {
                $fAmount = $total_fare;
            }
        } else {
            if ($eFareType == "Fixed") {
                $fAmount = round($Fare_data[0]['fFixedFare'] * $iQty, 2);
            } else if ($eFareType == "Hourly") {
                $fAmount = round($Fare_data[0]['fPricePerHour'] * $totalHour, 2);
            } else {
                $fAmount = $total_fare;
            }
        }
    } else {
        if ($eFareType == "Fixed") {
            $fAmount = round($Fare_data[0]['fFixedFare'] * $iQty, 2);
        } else if ($eFareType == "Hourly") {
            $fAmount = round($Fare_data[0]['fPricePerHour'] * $totalHour, 2);
        } else {
            $fAmount = $total_fare;
        }
    }

    $final_display_charge = $fAmount + $fVisitFee;
    $returnArr['Action'] = "1";
    /*$vCurrencyDriver=get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $tripData[0]['iDriverId'],'','true');
    $currencySymbolRationDriver = get_value('currency', 'vSymbol,Ratio', 'vName', $vCurrencyDriver);
    $returnArr['message']=$currencySymbolRationDriver[0]['vSymbol']." ".number_format(round($final_display_charge * $currencySymbolRationDriver[0]['Ratio'],1),2);*/
    //$currencySymbol = get_value('currency', 'vSymbol', 'eDefault', 'Yes','',true);
    $vCurrencyDriver = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $tripData[0]['iDriverId'], '', 'true');
    $currencySymbolRationDriver = get_value('currency', 'vSymbol,Ratio', 'vName', $vCurrencyDriver);
    $currencySymbol = $currencySymbolRationDriver[0]['vSymbol'];
    $currencyRationDriver = $currencySymbolRationDriver[0]['Ratio'];
    $final_display_charge = $final_display_charge * $currencyRationDriver;
    $final_display_charge = round($final_display_charge, 2);
    //$final_display_charge = formatNum($final_display_charge);
    $returnArr['message'] = $currencySymbol . ' ' . formatNum($final_display_charge);
    $returnArr['FareValue'] = $final_display_charge;
    $returnArr['CurrencySymbol'] = $currencySymbol;
    $obj->MySQLClose();
    echo json_encode($returnArr);exit;

?>