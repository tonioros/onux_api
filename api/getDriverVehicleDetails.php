<?php 
    $driverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Driver';
    $distance = isset($_REQUEST["distance"]) ? $_REQUEST["distance"] : '';
    $time = isset($_REQUEST["time"]) ? $_REQUEST["time"] : '';
    $StartLatitude = isset($_REQUEST["StartLatitude"]) ? $_REQUEST["StartLatitude"] : '0.0';
    $EndLongitude = isset($_REQUEST["EndLongitude"]) ? $_REQUEST["EndLongitude"] : '0.0';
    $DestLatitude = isset($_REQUEST["DestLatitude"]) ? $_REQUEST["DestLatitude"] : '';
    $DestLongitude = isset($_REQUEST["DestLongitude"]) ? $_REQUEST["DestLongitude"] : '';
    $PickUpAddress = isset($_REQUEST["PickUpAddress"]) ? $_REQUEST["PickUpAddress"] : '';
    $time = round(($time / 60), 2);
    $distance = round(($distance / 1000), 2);
    $VehicleTypeIds = isset($_REQUEST["VehicleTypeIds"]) ? $_REQUEST["VehicleTypeIds"] : '';

    $isDestinationAdded = "No";
    if ($DestLatitude != "" && $DestLongitude != "") {
        $isDestinationAdded = "Yes";
    }
    /*added for rental*/
    if ($userType == "Passenger") {
        $vCurrencyPassenger = get_value('register_user', 'vCurrencyPassenger', 'iUserId', $driverId, '', 'true');
        $vLang = get_value("register_user", "vLang", "iUserId", $driverId, '', 'true');
    } else {
        $vCurrencyPassenger = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $driverId, '', 'true');
        $vLang = get_value("register_driver", "vLang", "iDriverId", $driverId, '', 'true');
    }
    /*end added for rental*/
    //$vLang = get_value('register_driver', 'vLang', 'iDriverId', $driverId,'','true');
    if ($vLang == "" || $vLang == null) {
        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    /*added for rental*/
    if ($vCurrencyPassenger == "" || $vCurrencyPassenger == null) {
        $vCurrencyPassenger = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    }
    $priceRatio = get_value('currency', 'Ratio', 'vName', $vCurrencyPassenger, '', 'true');
    $vSymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyPassenger, '', 'true');
    /*end added for rental*/
    $iDriverVehicleId = get_value('register_driver', 'iDriverVehicleId', 'iDriverId', $driverId, '', 'true');
    if ($iDriverVehicleId > 0) {
        $Fare_Data = array();

        $vCarType = get_value('driver_vehicle', 'vCarType', 'iDriverVehicleId', $iDriverVehicleId, '', 'true');
        $DriverVehicle_Arr = explode(",", $vCarType);
        //echo "<pre>";print_r($DriverVehicle_Arr);echo "<br />";
        //$sql11 = "SELECT vVehicleType_".$vLang." as vVehicleTypeName, iVehicleTypeId, vLogo, iPersonSize FROM `vehicle_type`  WHERE  iVehicleTypeId IN (".$vCarType.") AND eType='Ride'";
        if ($VehicleTypeIds != "") {
            $sql11 = "SELECT  vVehicleType_" . $vLang . " as vVehicleTypeName,iVehicleTypeId,vRentalAlias_" . $vLang . " as vRentalVehicleTypeName,iVehicleTypeId, vLogo,vLogo1, iPersonSize FROM vehicle_type WHERE iVehicleTypeId IN (" . $VehicleTypeIds . ") AND eType='Ride'";
        } else {
            $pickuplocationarr = array($StartLatitude, $EndLongitude);
            $GetVehicleIdfromGeoLocation = GetVehicleTypeFromGeoLocation($pickuplocationarr);
            $sql_vehicle = "SELECT iVehicleTypeId FROM vehicle_type WHERE iLocationid IN (" . $GetVehicleIdfromGeoLocation . ") AND eType='Ride'";
            $db_vehicle_location = $obj->MySQLSelect($sql_vehicle);
            $array_vehiclie_id = array();
            for ($i = 0; $i < count($db_vehicle_location); $i++) {
                array_push($array_vehiclie_id, $db_vehicle_location[$i]['iVehicleTypeId']);
            }
            //echo "<pre>";print_r($array_vehiclie_id);echo "<br />";
            $Vehicle_array_diff = array_values(array_intersect($DriverVehicle_Arr, $array_vehiclie_id));
            $VehicleTypeIds_Str = implode(",", $Vehicle_array_diff);
            if ($VehicleTypeIds_Str == "") {
                $VehicleTypeIds_Str = "0";
            }
            $sql11 = "SELECT  vVehicleType_" . $vLang . " as vVehicleTypeName,vRentalAlias_" . $vLang . " as vRentalVehicleTypeName,iVehicleTypeId, vLogo,vLogo1, iPersonSize FROM vehicle_type WHERE iVehicleTypeId IN (" . $VehicleTypeIds_Str . ") AND eType='Ride'";
        }

        $vCarType_Arr = $obj->MySQLSelect($sql11);
        $Fare_Data = array();
        if (count($vCarType_Arr) > 0) {
            for ($i = 0; $i < count($vCarType_Arr); $i++) {
                ######### Checking For Flattrip #########
                if ($isDestinationAdded == "Yes") {
                    $sourceLocationArr = array($StartLatitude, $EndLongitude);
                    $destinationLocationArr = array($DestLatitude, $DestLongitude);
                    $data_flattrip = checkFlatTripnew($sourceLocationArr, $destinationLocationArr, $vCarType_Arr[$i]['iVehicleTypeId']);
                    $eFlatTrip = $data_flattrip['eFlatTrip'];
                    $fFlatTripPrice = $data_flattrip['Flatfare'];
                } else {
                    $eFlatTrip = "No";
                    $fFlatTripPrice = 0;
                }
                $Fare_Data[$i]['eFlatTrip'] = $eFlatTrip;
                $Fare_Data[$i]['fFlatTripPrice'] = $fFlatTripPrice;
                ######### Checking For Flattrip #########
                $Fare_Single_Vehicle_Data = calculateFareEstimateAll($time, $distance, $vCarType_Arr[$i]['iVehicleTypeId'], $driverId, 1, "", "", "", 1, 0, 0, 0, "DisplySingleVehicleFare", "Driver", 1, "", $isDestinationAdded, $eFlatTrip, $fFlatTripPrice);
                $Fare_Data[$i]['iVehicleTypeId'] = $vCarType_Arr[$i]['iVehicleTypeId'];
                $Fare_Data[$i]['vVehicleTypeName'] = $vCarType_Arr[$i]['vVehicleTypeName'];
                //$Fare_Data[$i]['vLogo'] = $vCarType_Arr[$i]['vLogo'];
                $Photo_Gallery_folder = $tconfig["tsite_upload_images_vehicle_type_path"] . '/' . $vCarType_Arr[$i]['iVehicleTypeId'] . '/android/' . $vCarType_Arr[$i]['vLogo'];
                if ($vCarType_Arr[$i]['vLogo'] != "" && file_exists($Photo_Gallery_folder)) {
                    $Fare_Data[$i]['vLogo'] = $vCarType_Arr[$i]['vLogo'];
                } else {
                    $Fare_Data[$i]['vLogo'] = "";
                }

                $Photo_Gallery_folder_vLogo1 = $tconfig["tsite_upload_images_vehicle_type_path"] . '/' . $vCarType_Arr[$i]['iVehicleTypeId'] . '/android/' . $vCarType_Arr[$i]['vLogo1'];
                if ($vCarType_Arr[$i]['vLogo1'] != "" && file_exists($Photo_Gallery_folder_vLogo1)) {
                    $Fare_Data[$i]['vLogo1'] = $vCarType_Arr[$i]['vLogo1'];
                } else {
                    $Fare_Data[$i]['vLogo1'] = "";
                }

                /*added for rental*/
                if (ENABLE_RENTAL_OPTION == 'Yes') {
                    if ($vCarType_Arr[$i]['vRentalVehicleTypeName'] != '') {
                        $Fare_Data[$i]['vRentalVehicleTypeName'] = $vCarType_Arr[$i]['vRentalVehicleTypeName'];
                    } else {
                        $Fare_Data[$i]['vRentalVehicleTypeName'] = $vCarType_Arr[$i]['vVehicleTypeName'];
                    }
                    $checkrentalquery = "SELECT iRentalPackageId,iVehicleTypeId,vPackageName_" . $vLang . ",fPrice,fKiloMeter,fHour,fPricePerKM,fPricePerHour FROM  `rental_package` WHERE iVehicleTypeId = '" . $Fare_Data[$i]['iVehicleTypeId'] . "' ORDER BY `fPrice` ASC";
                    $rental_data = $obj->MySQLSelect($checkrentalquery);
                    if (count($rental_data) > 0) {
                        if ($userType == 'Driver') {
                            $rentquery = "SELECT `vRentalCarType` FROM `driver_vehicle` WHERE  iDriverVehicleId = '" . $iDriverVehicleId . "' AND FIND_IN_SET ('" . $Fare_Data[$i]['iVehicleTypeId'] . "', vRentalCarType)";
                            $rentalData_Arr = $obj->MySQLSelect($rentquery);
                            if (count($rentalData_Arr) > 0) {
                                $Fare_Data[$i]['eRental'] = 'Yes';
                                $Fare_Data[$i]['RentalSubtotal'] = $vSymbol . " " . number_format(round($rental_data[0]['fPrice'] * $priceRatio, 1), 2);
                            } else {
                                $Fare_Data[$i]['eRental'] = 'No';
                            }
                        } else {
                            $Fare_Data[$i]['eRental'] = 'Yes';
                        }
                    } else {
                        $Fare_Data[$i]['eRental'] = 'No';
                    }
                } else {
                    $Fare_Data[$i]['eRental'] = 'No';
                }
                /*End added for rental*/
                $Fare_Data[$i]['iPersonSize'] = $vCarType_Arr[$i]['iPersonSize'];
                $lastvalue = end($Fare_Single_Vehicle_Data);
                $lastvalue1 = array_shift($lastvalue);
                $Fare_Data[$i]['SubTotal'] = $lastvalue1;
                $Fare_Data[$i]['VehicleFareDetail'] = $Fare_Single_Vehicle_Data;
                //array_push($Fare_Data, $Fare_Single_Vehicle_Data);

            }
        }
        $returnArr['Action'] = "1";
        $returnArr['message'] = $Fare_Data;
        //$returnArr['eFlatTrip'] = $eFlatTrip;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_VEHICLE_SELECTED";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);exit;

?>