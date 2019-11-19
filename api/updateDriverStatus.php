<?php 

    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $Status_driver = isset($_REQUEST["Status"]) ? $_REQUEST["Status"] : '';
    $isUpdateOnlineDate = isset($_REQUEST["isUpdateOnlineDate"]) ? $_REQUEST["isUpdateOnlineDate"] : '';
    $latitude_driver = isset($_REQUEST["latitude"]) ? $_REQUEST["latitude"] : '';
    $longitude_driver = isset($_REQUEST["longitude"]) ? $_REQUEST["longitude"] : '';
    $iGCMregID = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    //$APP_TYPE = $generalobj->getConfigurations("configurations", "APP_TYPE");
    //$APP_PAYMENT_MODE = $generalobj->getConfigurations("configurations", "APP_PAYMENT_MODE");

    if ($Status_driver == "Available") {
        checkmemberemailphoneverification($iDriverId, "Driver");
    }

    if ($iDriverId == '') {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        echo json_encode($returnArr);
        exit;
    }

    $GCMID = get_value('register_driver', 'iGcmRegId', 'iDriverId', $iDriverId, '', 'true');
    if ($GCMID != "" && $iGCMregID != "" && $GCMID != $iGCMregID) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "SESSION_OUT";
        echo json_encode($returnArr);
        exit;
    }
    $returnArr['Enable_Hailtrip'] = "No";

    //$COMMISION_DEDUCT_ENABLE=$generalobj->getConfigurations("configurations","COMMISION_DEDUCT_ENABLE");
    if ($COMMISION_DEDUCT_ENABLE == 'Yes' && ($APP_PAYMENT_MODE == "Cash" || $APP_PAYMENT_MODE == "Cash-Card")) {
        $vLang = get_value('register_driver', 'vLang', 'iDriverId', $iDriverId, '', 'true');
        if ($vLang == "" || $vLang == null) {
            $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        $languageLabelsArr = getLanguageLabelsArr($vLang, "1");
        $user_available_balance = $generalobj->get_user_available_balance($iDriverId, "Driver");
        $driverDetail = get_value('register_driver AS rd LEFT JOIN currency AS c ON c.vName=rd.vCurrencyDriver', 'rd.vCurrencyDriver,c.Ratio,c.vSymbol', 'rd.iDriverId', $iDriverId);
        $vCurrencyDriver = $driverDetail[0]['vCurrencyDriver'];
        $ratio = $driverDetail[0]['Ratio'];
        $currencySymbol = $driverDetail[0]['vSymbol'];
        //$WALLET_MIN_BALANCE=$generalobj->getConfigurations("configurations","WALLET_MIN_BALANCE");
        if ($WALLET_MIN_BALANCE > $user_available_balance) {
            // $returnArr['Action'] = "0";
            $returnArr['message'] = "REQUIRED_MINIMUM_BALNCE";
            if ($APP_TYPE == "UberX") {
                $returnArr['Msg'] = str_replace('####', $currencySymbol . ($WALLET_MIN_BALANCE * $ratio), $languageLabelsArr['LBL_REQUIRED_MINIMUM_BALNCE_UBERX']);
            } else {
                $returnArr['Msg'] = str_replace('####', $currencySymbol . ($WALLET_MIN_BALANCE * $ratio), $languageLabelsArr['LBL_REQUIRED_MINIMUM_BALNCE']);
            }

            if ($APP_PAYMENT_MODE == "Cash") {
                if ($Status_driver == "Available") {
                    $returnArr['Action'] = "0";
                    echo json_encode($returnArr);
                    exit;
                }
            }
        }
        $returnArr['Enable_Hailtrip'] = "Yes";
    }

    if ($COMMISION_DEDUCT_ENABLE == 'No' && ($APP_PAYMENT_MODE == "Cash" || $APP_PAYMENT_MODE == "Cash-Card") && $APP_TYPE != "UberX") {
        $returnArr['Enable_Hailtrip'] = "Yes";
    }

    // getDriverStatus($iDriverId);
    //$APP_TYPE = $generalobj->getConfigurations("configurations", "APP_TYPE");
    $ssql = "";
    $CheckRideDeliveryFeatureDisable_Arr = CheckRideDeliveryFeatureDisable();
    $eShowRideVehicles = $CheckRideDeliveryFeatureDisable_Arr['eShowRideVehicles'];
    $eShowDeliveryVehicles = $CheckRideDeliveryFeatureDisable_Arr['eShowDeliveryVehicles'];
    $RideDeliveryBothFeatureDisable = $CheckRideDeliveryFeatureDisable_Arr['RideDeliveryBothFeatureDisable'];
    if ($APP_TYPE == "UberX" || $APP_TYPE == "Ride-Delivery-UberX") {
        //$ssql = "And dv.vCarType !=''";
        if ($eShowRideVehicles == "No" && $eShowDeliveryVehicles == "No") {
            //$ssql.= " AND ( dv.eType = 'UberX')";
            $ssql .= "And dv.vCarType !=''";
        } else if ($eShowRideVehicles == "No" && $eShowDeliveryVehicles == "Yes") {
            $ssql .= " AND ( dv.eType = 'Delivery' OR dv.eType = 'UberX')";
        } else if ($eShowRideVehicles == "Yes" && $eShowDeliveryVehicles == "No") {
            $ssql .= " AND ( dv.eType = 'Ride' OR dv.eType = 'UberX')";
        } else {
            //$ssql.= " AND ( dv.eType = 'Delivery' OR dv.eType = 'Ride' OR dv.eType = 'UberX')";
            $ssql .= "And dv.vCarType !=''";
        }
    }

    $sql = "SELECT make.vMake, model.vTitle, dv.*, rd.iDriverVehicleId as iSelectedVehicleId FROM `driver_vehicle` dv, make, model, register_driver as rd WHERE dv.iDriverId='$iDriverId' AND rd.iDriverId='$iDriverId' AND dv.`iMakeId` = make.`iMakeId` AND dv.`iModelId` = model.`iModelId` AND dv.`eStatus`='Active'" . $ssql;
    $Data_Car = $obj->MySQLSelect($sql);

    if (count($Data_Car) > 0) {
        if (count($Data_Car) == 1 && $Data_Car[0]['eType'] == "UberX") {
            /*if($Status_driver == "Available"){
            $returnArr['UberX_message']="LBL_PROVIDER_OTHER_SERVICE_ENABLE_TXT";
            }  */
            $returnArr['Enable_Hailtrip'] = "No";
        } else {
            $status = "CARS_NOT_ACTIVE";

            $i = 0;
            while (count($Data_Car) > $i) {

                $eStatus = $Data_Car[$i]['eStatus'];
                if ($eStatus == "Active") {
                    $status = "CARS_AVAIL";
                }

                if (($Data_Car[0]['iSelectedVehicleId'] == $Data_Car[$i]['iDriverVehicleId']) && $returnArr['Enable_Hailtrip'] == "Yes") {
                    $DriverCarTypes = $Data_Car[$i]['vCarType'];
                    $sql = "SELECT iVehicleTypeId,eType  FROM `vehicle_type` WHERE `iVehicleTypeId` IN ($DriverCarTypes)";
                    $db_cartype = $obj->MySQLSelect($sql);
                    $enable_hail_flag = "No";
                    if (count($db_cartype) > 0) {
                        for ($j = 0; $j < count($db_cartype); $j++) {
                            $eType = $db_cartype[$j]['eType'];
                            if ($eType == "Ride") {
                                $enable_hail_flag = "Yes";
                            }
                        }
                    }
                    $returnArr['Enable_Hailtrip'] = ($enable_hail_flag == "Yes") ? "Yes" : "No";
                }

                $i++;
            }

            if ($status == "CARS_AVAIL" && ($Data_Car[0]['iSelectedVehicleId'] == "0" || $Data_Car[0]['iSelectedVehicleId'] == "") && $Status_driver == "Available") {
                // echo "SELECT_CAR";
                if ($APP_TYPE == "Ride-Delivery-UberX") {
                    $sql = "SELECT vCarType from driver_vehicle WHERE iDriverId = '" . $iDriverId . "' AND eType = 'UberX'";
                    $db_cartype = $obj->MySQLSelect($sql);
                    $vCarType = $db_cartype[0]['vCarType'];
                    if ($vCarType == "") {
                        $returnArr['Action'] = "0";
                        $returnArr['Enable_Hailtrip'] = "No";
                        //$returnArr['message']="LBL_PROVIDER_NO_SERVICE_ENABLE_TXT";
                        $returnArr['message'] = ($RideDeliveryBothFeatureDisable == "No") ? "LBL_PROVIDER_NO_SERVICE_ENABLE_TXT" : "LBL_ONLY_OTHER_SERVICE_ENABLE_TXT";
                        echo json_encode($returnArr);
                        exit;
                    } else {
                        $returnArr['Enable_Hailtrip'] = "No";
                        //$returnArr['UberX_message']="LBL_PROVIDER_OTHER_SERVICE_ENABLE_TXT";
                        $returnArr['UberX_message'] = ($RideDeliveryBothFeatureDisable == "No") ? "LBL_PROVIDER_OTHER_SERVICE_ENABLE_TXT" : "LBL_ONLY_OTHER_SERVICE_ENABLE_TXT";
                    }
                } else {
                    $returnArr['Action'] = "0";
                    $returnArr['message'] = "LBL_SELECT_CAR_MESSAGE_TXT";
                    echo json_encode($returnArr);
                    exit;
                }
            } else if ($status == "CARS_NOT_ACTIVE") {
                // echo "CARS_NOT_ACTIVE";
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_INACTIVE_CARS_MESSAGE_TXT";
                $returnArr['Enable_Hailtrip'] = "No";
                echo json_encode($returnArr);
                exit;
            }

        }
    } else {
        if ($APP_TYPE == "Ride-Delivery-UberX" || $APP_TYPE == "UberX") {
            $sql = "SELECT vCarType from driver_vehicle WHERE iDriverId = '" . $iDriverId . "' AND eType = 'UberX'";
            $db_cartype = $obj->MySQLSelect($sql);
            $vCarType = $db_cartype[0]['vCarType'];
            if ($vCarType == "" && count($db_cartype) > 0) {
                $returnArr['Action'] = "0";
                $returnArr['Enable_Hailtrip'] = "No";
                if ($APP_TYPE == "UberX") {
                    $returnArr['message'] = "LBL_NO_SERVICE_AVAIL";
                } else {
                    $returnArr['message'] = "LBL_PROVIDER_NO_SERVICE_ENABLE_TXT";
                }
                echo json_encode($returnArr);
                exit;
            }
        }

        // echo "NO_CARS_AVAIL";
        $sql = "SELECT count(iDriverVehicleId) as TotalVehicles from driver_vehicle WHERE iDriverId = '" . $iDriverId . "' AND ( eStatus = 'Inactive' OR eStatus = 'Deleted')";
        $db_Total_vehicle = $obj->MySQLSelect($sql);
        $TotalVehicles = $db_Total_vehicle[0]['TotalVehicles'];
        $returnArr['Action'] = "0";
        if ($TotalVehicles == 0) {
            $returnArr['Enable_Hailtrip'] = "No";
            $returnArr['message'] = "LBL_NO_CAR_AVAIL_TXT";
        } else {
            $returnArr['message'] = "LBL_INACTIVE_CARS_MESSAGE_TXT";
        }
        echo json_encode($returnArr);
        exit;
    }

    $where = " iDriverId='" . $iDriverId . "'";
    if ($Status_driver != '') {
        $Data_update_driver['vAvailability'] = $Status_driver;
    }

    if ($latitude_driver != '' && $longitude_driver != '') {
        $Data_update_driver['vLatitude'] = $latitude_driver;
        $Data_update_driver['vLongitude'] = $longitude_driver;
    }

    if ($Status_driver == "Available") {
        $Data_update_driver['tOnline'] = @date("Y-m-d H:i:s");
        // insert as online
        // Code for Check last logout date is update in driver_log_report
        $query = "SELECT * FROM driver_log_report WHERE dLogoutDateTime = '0000-00-00 00:00:00' AND iDriverId = '" . $iDriverId . "' ORDER BY iDriverLogId DESC LIMIT 0,1";
        $db_driver = $obj->MySQLSelect($query);
        if (count($db_driver) > 0) {
            $sql = "SELECT tLastOnline FROM register_driver WHERE iDriverId = '" . $iDriverId . "'";
            $db_drive_lastonline = $obj->MySQLSelect($sql);
            $driver_lastonline = $db_drive_lastonline[0]['tLastOnline'];
            $updateQuery = "UPDATE driver_log_report set dLogoutDateTime='" . $driver_lastonline . "' WHERE iDriverLogId = " . $db_driver[0]['iDriverLogId'];
            $obj->sql_query($updateQuery);
        }
        // Code for Check last logout date is update in driver_log_report Ends
        $vIP = get_client_ip();
        $curr_date = date('Y-m-d H:i:s');
        $sql = "INSERT INTO `driver_log_report` (`iDriverId`,`dLoginDateTime`,`vIP`) VALUES ('" . $iDriverId . "','" . $curr_date . "','" . $vIP . "')";
        $insert_log = $obj->sql_query($sql);
    }

    if ($Status_driver == "Not Available") {
        // update as offline
        $Data_update_driver['tLastOnline'] = @date("Y-m-d H:i:s");
        $curr_date = date('Y-m-d H:i:s');
        $selct_query = "select * from driver_log_report WHERE iDriverId = '" . $iDriverId . "' order by `iDriverLogId` desc limit 0,1";
        $get_data_log = $obj->sql_query($selct_query);

        $update_sql = "UPDATE driver_log_report set dLogoutDateTime = '" . $curr_date . "' WHERE iDriverLogId ='" . $get_data_log[0]['iDriverLogId'] . "'";
        $result = $obj->sql_query($update_sql);
    }

    if (($isUpdateOnlineDate == "true" && $Status_driver == "Available") || ($isUpdateOnlineDate == "" && $Status_driver == "") || $isUpdateOnlineDate == "true") {
        $Data_update_driver['tOnline'] = @date("Y-m-d H:i:s");
        $Data_update_driver['tLastOnline'] = @date("Y-m-d H:i:s");
    }

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

    # Update User Location Date #
    Updateuserlocationdatetime($iDriverId, "Driver", $vTimeZone);
    # Update User Location Date #
    if ($APP_TYPE == "Ride-Delivery-UberX" || $APP_TYPE == "UberX") {
        $isExistUberXServices = "Yes";
        $sql = "SELECT vCarType from driver_vehicle WHERE iDriverId = '" . $iDriverId . "' AND eType = 'UberX'";
        $db_cartype = $obj->MySQLSelect($sql);
        $vCarType = $db_cartype[0]['vCarType'];
        if ($vCarType == "") {
            $isExistUberXServices = "No";
        }
        $returnArr['isExistUberXServices'] = $isExistUberXServices;
    }

    if ($ENABLE_HAIL_RIDES == "No") {
        $returnArr['Enable_Hailtrip'] = "No";
    }
    if ($APP_TYPE != "Ride-Delivery-UberX" && $APP_TYPE != "UberX") {
        $returnArr['isExistUberXServices'] = "No";
    }

    if ($id) {
        $returnArr['Action'] = "1";
        echo json_encode($returnArr);
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        echo json_encode($returnArr);
    }


?>