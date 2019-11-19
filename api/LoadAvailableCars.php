<?php 
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';

    $CheckRideDeliveryFeatureDisable_Arr = CheckRideDeliveryFeatureDisable();
    $eShowRideVehicles = $CheckRideDeliveryFeatureDisable_Arr['eShowRideVehicles'];
    $eShowDeliveryVehicles = $CheckRideDeliveryFeatureDisable_Arr['eShowDeliveryVehicles'];
    $RideDeliveryBothFeatureDisable = $CheckRideDeliveryFeatureDisable_Arr['RideDeliveryBothFeatureDisable'];
    $ssql = "";
    if ($APP_TYPE == "Delivery") {
        $ssql .= " AND dv.eType = 'Delivery'";
    } else if ($APP_TYPE == "Ride-Delivery") {
        $ssql .= " AND ( dv.eType = 'Delivery' OR dv.eType = 'Ride')";
    } else if ($APP_TYPE == "Ride-Delivery-UberX") {
        if ($eShowRideVehicles == "No" && $eShowDeliveryVehicles == "No") {
            $ssql .= " AND ( dv.eType = 'UberX')";
        } else if ($eShowRideVehicles == "No" && $eShowDeliveryVehicles == "Yes") {
            $ssql .= " AND ( dv.eType = 'Delivery' OR dv.eType = 'UberX')";
        } else if ($eShowRideVehicles == "Yes" && $eShowDeliveryVehicles == "No") {
            $ssql .= " AND ( dv.eType = 'Ride' OR dv.eType = 'UberX')";
        } else {
            $ssql .= " AND ( dv.eType = 'Delivery' OR dv.eType = 'Ride' OR dv.eType = 'UberX')";
        }
    } else {
        $ssql .= " AND dv.eType = '" . $APP_TYPE . "'";
    }

    $sql = "SELECT register_driver.iDriverVehicleId as DriverSelectedVehicleId,make.vMake, model.vTitle, dv.* FROM `driver_vehicle` dv, make, model,register_driver WHERE dv.iDriverId='$iDriverId' AND register_driver.iDriverId = '$iDriverId' AND dv.`iMakeId` = make.`iMakeId` AND dv.`iModelId` = model.`iModelId` AND dv.`eStatus`='Active'" . $ssql;
    //$sql = "SELECT register_driver.iDriverVehicleId as DriverSelectedVehicleId,make.vMake, model.vTitle, dv.* FROM `driver_vehicle` dv, make, model,register_driver WHERE dv.iDriverId='$iDriverId' AND register_driver.iDriverId = '$iDriverId' AND dv.`iMakeId` = make.`iMakeId` AND dv.`iModelId` = model.`iModelId`";

    $Data_Car = $obj->MySQLSelect($sql);

    if (count($Data_Car) > 0) {

        $sql = "SELECT count(dv.iDriverVehicleId) as TotalVehicles from driver_vehicle as dv WHERE iDriverId = '" . $iDriverId . "'" . $ssql;
        $db_Total_vehicle = $obj->MySQLSelect($sql);
        $TotalVehicles = $db_Total_vehicle[0]['TotalVehicles'];

        if (count($Data_Car) == 1 && $Data_Car[0]['eType'] == "UberX" && $TotalVehicles == 1 && $APP_TYPE = "Ride-Delivery-UberX") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = ($RideDeliveryBothFeatureDisable == "No") ? "LBL_PROVIDER_OTHER_SERVICE_ENABLE_TXT" : "LBL_ONLY_OTHER_SERVICE_ENABLE_TXT";
            echo json_encode($returnArr);
            exit;
        } else {
            $status = "CARS_NOT_ACTIVE";

            $i = 0;
            while (count($Data_Car) > $i) {

                $eStatus = $Data_Car[$i]['eStatus'];
                if ($eStatus == "Active") {
                    $status = "CARS_AVAIL";
                }
                $i++;
            }
            if ($status == "CARS_NOT_ACTIVE") {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_INACTIVE_CARS_MESSAGE_TXT";
                echo json_encode($returnArr);
                exit;
            }

            // $returnArr['carList'] = $Data_Car;
            $db_vehicle_new = $Data_Car;
            for ($i = 0; $i < count($Data_Car); $i++) {
                $eType = $Data_Car[$i]['eType'];
                if ($eType == "UberX") {
                    unset($db_vehicle_new[$i]);
                }
                /*$vCarType = $Data_Car[$i]['vCarType'];
            if($vCarType == ""){
            $vCarType = 0;
            }
            $sql = "SELECT iVehicleTypeId,eType  FROM `vehicle_type` WHERE `iVehicleTypeId` IN ($vCarType)";
            $db_cartype = $obj->MySQLSelect($sql);
            $k=0;
            if (count($db_cartype) > 0) {
            for($j=0;$j<count($db_cartype);$j++){
            $eType = $db_cartype[$j]['eType'];
            if($eType == "UberX"){
            unset($db_vehicle_new[$i]);
            }
            }
            }  */
            }
            $db_vehicle_new = array_values($db_vehicle_new);

            if (count($db_vehicle_new) == 0) {
                $sql = "SELECT count(iDriverVehicleId) as TotalVehicles from driver_vehicle WHERE iDriverId = '" . $driverId . "' AND eStatus = 'Inactive'";
                $db_tot_vehicle = $obj->MySQLSelect($sql);
                $TotalVehicles = $db_tot_vehicle[0]['TotalVehicles'];
                $returnArr['Action'] = "0";
                if ($TotalVehicles > 0) {
                    $returnArr['message'] = "LBL_INACTIVE_CARS_MESSAGE_TXT";
                } else {
                    $returnArr['message'] = "LBL_NO_CAR_AVAIL_TXT";
                }
                echo json_encode($returnArr);exit;
            }

            for ($i = 0; $i < count($db_vehicle_new); $i++) {
                //$db_vehicle_new[$i]['Enable_Hailtrip'] = CheckHailTripEnable($db_vehicle_new[$i]['iDriverVehicleId'],$iDriverId);
                if ($db_vehicle_new[$i]['eType'] == "Ride" && $APP_PAYMENT_MODE != "Card") {
                    $db_vehicle_new[$i]['Enable_Hailtrip'] = "Yes";
                } else {
                    $db_vehicle_new[$i]['Enable_Hailtrip'] = "No";
                }

                if ($ENABLE_HAIL_RIDES == "No") {
                    $db_vehicle_new[$i]['Enable_Hailtrip'] = "No";
                }
            }

            // echo json_encode($returnArr);
            $returnArr['Action'] = "1";
            $returnArr['message'] = $db_vehicle_new;
            echo json_encode($returnArr);
        }

    } else {
        $sql = "SELECT count(iDriverVehicleId) as TotalVehicles from driver_vehicle WHERE iDriverId = '" . $driverId . "' AND ( eStatus = 'Inactive' OR eStatus = 'Deleted')";
        $db_Total_vehicle = $obj->MySQLSelect($sql);
        $TotalVehicles = $db_Total_vehicle[0]['TotalVehicles'];
        $returnArr['Action'] = "0";
        if ($TotalVehicles == 0) {
            $returnArr['message'] = "LBL_NO_CAR_AVAIL_TXT";
        } else {
            $returnArr['message'] = "LBL_INACTIVE_CARS_MESSAGE_TXT";
        }

        echo json_encode($returnArr);
        exit;
    }
