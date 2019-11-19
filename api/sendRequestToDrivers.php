<?php 

    $driver_id_auto = isset($_REQUEST["driverIds"]) ? $_REQUEST["driverIds"] : '';
    $message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : '';
    $passengerId = isset($_REQUEST["userId"]) ? $_REQUEST["userId"] : '';
    $cashPayment = isset($_REQUEST["CashPayment"]) ? $_REQUEST["CashPayment"] : '';
    $selectedCarTypeID = isset($_REQUEST["SelectedCarTypeID"]) ? $_REQUEST["SelectedCarTypeID"] : '';

    $eFemaleDriverRequest = isset($_REQUEST["eFemaleDriverRequest"]) ? $_REQUEST["eFemaleDriverRequest"] : '';
    $eHandiCapAccessibility = isset($_REQUEST["eHandiCapAccessibility"]) ? $_REQUEST["eHandiCapAccessibility"] : '';
    $PickUpLatitude = isset($_REQUEST["PickUpLatitude"]) ? $_REQUEST["PickUpLatitude"] : '0.0';
    $PickUpLongitude = isset($_REQUEST["PickUpLongitude"]) ? $_REQUEST["PickUpLongitude"] : '0.0';
    $PickUpAddress = isset($_REQUEST["PickUpAddress"]) ? $_REQUEST["PickUpAddress"] : '';

    $DestLatitude = isset($_REQUEST["DestLatitude"]) ? $_REQUEST["DestLatitude"] : '';
    $DestLongitude = isset($_REQUEST["DestLongitude"]) ? $_REQUEST["DestLongitude"] : '';
    $DestAddress = isset($_REQUEST["DestAddress"]) ? $_REQUEST["DestAddress"] : '';
    $promoCode = isset($_REQUEST["PromoCode"]) ? $_REQUEST["PromoCode"] : '';
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : '';
    $iPackageTypeId = isset($_REQUEST["iPackageTypeId"]) ? $_REQUEST["iPackageTypeId"] : '';
    $vReceiverName = isset($_REQUEST["vReceiverName"]) ? $_REQUEST["vReceiverName"] : '';
    $vReceiverMobile = isset($_REQUEST["vReceiverMobile"]) ? $_REQUEST["vReceiverMobile"] : '';
    $tPickUpIns = isset($_REQUEST["tPickUpIns"]) ? $_REQUEST["tPickUpIns"] : '';
    $tDeliveryIns = isset($_REQUEST["tDeliveryIns"]) ? $_REQUEST["tDeliveryIns"] : '';
    $tPackageDetails = isset($_REQUEST["tPackageDetails"]) ? $_REQUEST["tPackageDetails"] : '';
    $vDeviceToken = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $iUserPetId = isset($_REQUEST["iUserPetId"]) ? $_REQUEST["iUserPetId"] : '0';
    $quantity = isset($_REQUEST["Quantity"]) ? $_REQUEST["Quantity"] : '';
    $PickUpAddress = isset($_REQUEST["PickUpAddress"]) ? $_REQUEST["PickUpAddress"] : '';
    $fTollPrice = isset($_REQUEST["fTollPrice"]) ? $_REQUEST["fTollPrice"] : '';
    $vTollPriceCurrencyCode = isset($_REQUEST["vTollPriceCurrencyCode"]) ? $_REQUEST["vTollPriceCurrencyCode"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $eTollSkipped = isset($_REQUEST["eTollSkipped"]) ? $_REQUEST["eTollSkipped"] : 'Yes';
    $iUserAddressId = isset($_REQUEST["iUserAddressId"]) ? $_REQUEST["iUserAddressId"] : '0';
    $tUserComment = isset($_REQUEST["tUserComment"]) ? $_REQUEST["tUserComment"] : '';

	

    /*added for rental*/
    $iRentalPackageId = isset($_REQUEST["iRentalPackageId"]) ? $_REQUEST["iRentalPackageId"] : '';
    $trip_status = "Requesting";

    if ($APP_TYPE == "Ride-Delivery-UberX") {
        $sqldata = "SELECT iTripId FROM `trips` WHERE iActive='On Going Trip'  AND iUserId='" . $passengerId . "' AND eType != 'UberX'";
        $TripData = $obj->MySQLSelect($sqldata);
        if (count($TripData) > 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_ONGOING_TRIP_USER_TXT";
            echo json_encode($returnArr);exit;
        }
    }

    /*$iCabRequestId_cab_now= get_value('cab_request_now', 'max(iCabRequestId)', 'iUserId',$passengerId,'','true');
    $eStatus_cab_now= get_value('cab_request_now', 'eStatus', 'iCabRequestId',$iCabRequestId_cab_now,'','true');*/
    $sql_cabrequest = "SELECT iCabRequestId,eStatus FROM `cab_request_now` WHERE iUserId='" . $passengerId . "' ORDER BY iCabRequestId DESC LIMIT 0,1";
    $Data_cabrequest = $obj->MySQLSelect($sql_cabrequest);
    $iCabRequestId_cab_now = $Data_cabrequest[0]['iCabRequestId'];
    $eStatus_cab_now = $Data_cabrequest[0]['eStatus'];
    if ($eStatus_cab_now == "Requesting") {
        $where_cab_now = " iCabRequestId = '$iCabRequestId_cab_now' ";
        $Data_update_cab_now['eStatus'] = "Cancelled";

        $obj->MySQLQueryPerform("cab_request_now", $Data_update_cab_now, 'update', $where_cab_now);
    }

    checkmemberemailphoneverification($passengerId, "Passenger");
    ## check pickup addresss for UberX #
    //$APP_TYPE = $generalobj->getConfigurations("configurations","APP_TYPE");
    if ($eType == "") {
        $eType = $APP_TYPE == "Delivery" ? "Deliver" : $APP_TYPE;
    }
    if ($eType == "UberX") {
        $Data_update_passenger['tUserComment'] = $tUserComment;
        //$PickUpAddress=get_value('user_address', 'vServiceAddress', '    iUserAddressId',$iUserAddressId,'','true');
        if ($iUserAddressId != "") {
            $Address = get_value('user_address', 'vAddressType,vBuildingNo,vLandmark,vServiceAddress,vLatitude,vLongitude', '	iUserAddressId', $iUserAddressId, '', '');
            $vAddressType = $Address[0]['vAddressType'];
            $vBuildingNo = $Address[0]['vBuildingNo'];
            $vLandmark = $Address[0]['vLandmark'];
            $vServiceAddress = $Address[0]['vServiceAddress'];
            $PickUpAddress = ($vAddressType != "") ? $vAddressType . "\n" : "";
            $PickUpAddress .= ($vBuildingNo != "") ? $vBuildingNo . "," : "";
            $PickUpAddress .= ($vLandmark != "") ? $vLandmark . "\n" : "";
            $PickUpAddress .= ($vServiceAddress != "") ? $vServiceAddress : "";
            $Data_update_passenger['tSourceAddress'] = $PickUpAddress;
            $Data_update_passenger['iUserAddressId'] = $iUserAddressId;
            $PickUpLatitude = $Address[0]['vLatitude'];
            $PickUpLongitude = $Address[0]['vLongitude'];
        } else {
            $Data_update_passenger['tSourceAddress'] = $PickUpAddress;
        }
    } else {
        $Data_update_passenger['tSourceAddress'] = $PickUpAddress;
    }
    ## check pickup addresss for UberX #
    ### Checking For Pickup And DropOff Disallow ###
    $pickuplocationarr = array($PickUpLatitude, $PickUpLongitude);
    $allowed_ans_pickup = checkAllowedAreaNew($pickuplocationarr, "No");
    if ($allowed_ans_pickup == "No") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_PICKUP_LOCATION_NOT_ALLOW";
        echo json_encode($returnArr);
        exit;
    }
    if ($DestLatitude != "" && $DestLongitude != "") {
        $dropofflocationarr = array($DestLatitude, $DestLongitude);
        $allowed_ans_dropoff = checkAllowedAreaNew($dropofflocationarr, "Yes");
        if ($allowed_ans_dropoff == "No") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_DROP_LOCATION_NOT_ALLOW";
            echo json_encode($returnArr);
            exit;
        }
    }
    ### Checking For Pickup And DropOff Disallow ###

    $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
    $userwaitinglabel = $languageLabelsArr['LBL_TRIP_USER_WAITING'];
    if ($eType == "UberX") {
        $alertMsg = $languageLabelsArr['LBL_USER_WAITING'];
    } elseif ($eType == "Ride") {
        $alertMsg = $userwaitinglabel;
    } else {
        $alertMsg = $languageLabelsArr['LBL_DELIVERY_SENDER_WAITING'];
    }

    $address_data['PickUpAddress'] = $PickUpAddress;
    $address_data['DropOffAddress'] = $DestAddress;
    if ($DestLatitude != "" && $DestLongitude != "") {
        $DropOff = "Yes";
    } else {
        $DropOff = "No";
    }
    $DataArr = getOnlineDriverArr($PickUpLatitude, $PickUpLongitude, $address_data, $DropOff, "No", "No", "", $DestLatitude, $DestLongitude, $eType);
    $Data = $DataArr['DriverList'];
    if ($DataArr['PickUpDisAllowed'] == "No" && $DataArr['DropOffDisAllowed'] == "No") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_PICK_DROP_LOCATION_NOT_ALLOW";
        echo json_encode($returnArr);
        exit;
    }
    if ($DataArr['PickUpDisAllowed'] == "Yes" && $DataArr['DropOffDisAllowed'] == "No") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_DROP_LOCATION_NOT_ALLOW";
        echo json_encode($returnArr);
        exit;
    }
    if ($DataArr['PickUpDisAllowed'] == "No" && $DataArr['DropOffDisAllowed'] == "Yes") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_PICKUP_LOCATION_NOT_ALLOW";
        echo json_encode($returnArr);
        exit;
    }

    $sqlp = "SELECT iGcmRegId,vName,vLastName,vImgName,vFbId,vAvgRating,vPhone,vPhoneCode FROM register_user WHERE iUserId = '" . $passengerId . "'";
    $passengerData = $obj->MySQLSelect($sqlp);
    //$iGcmRegId=get_value('register_user', 'iGcmRegId', 'iUserId',$passengerId,'','true');
    $iGcmRegId = $passengerData[0]['iGcmRegId'];

    if ($vDeviceToken != "" && $vDeviceToken != $iGcmRegId) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "SESSION_OUT";
        echo json_encode($returnArr);
        exit;
    }

    $final_message['Message'] = "CabRequested";
    $final_message['sourceLatitude'] = strval($PickUpLatitude);
    $final_message['sourceLongitude'] = strval($PickUpLongitude);
    $final_message['PassengerId'] = strval($passengerId);
    /*$passengerFName = get_value('register_user', 'vName', 'iUserId',$passengerId,'','true');
    $passengerLName = get_value('register_user', 'vLastName', 'iUserId',$passengerId,'','true');
    $final_message['PName'] = $passengerFName. " " .$passengerLName;
    $final_message['PPicName'] = get_value('register_user', 'vImgName', 'iUserId',$passengerId,'','true');
    $final_message['PFId'] = get_value('register_user', 'vFbId', 'iUserId',$passengerId,'','true');
    $final_message['PRating'] = get_value('register_user', 'vAvgRating', 'iUserId',$passengerId,'','true');
    $final_message['PPhone'] = get_value('register_user', 'vPhone', 'iUserId',$passengerId,'','true');
    $final_message['PPhoneC'] = get_value('register_user', 'vPhoneCode', 'iUserId',$passengerId,'','true'); */
    $passengerFName = $passengerData[0]['vName'];
    $passengerLName = $passengerData[0]['vLastName'];
    $final_message['PName'] = $passengerFName . " " . $passengerLName;
    $final_message['PPicName'] = $passengerData[0]['vImgName'];
    $final_message['PFId'] = $passengerData[0]['vFbId'];
    $final_message['PRating'] = $passengerData[0]['vAvgRating'];
    $final_message['PPhone'] = $passengerData[0]['vPhone'];
    $final_message['PPhoneC'] = $passengerData[0]['vPhoneCode'];
    $final_message['PPhone'] = '+' . $final_message['PPhoneC'] . $final_message['PPhone'];
    $final_message['REQUEST_TYPE'] = $eType;
    // packagename changes
    //$final_message['PACKAGE_TYPE'] = $eType == "Deliver"?get_value('package_type', 'vName', 'iPackageTypeId',$iPackageTypeId,'','true'):'';
    $final_message['destLatitude'] = strval($DestLatitude);
    $final_message['destLongitude'] = strval($DestLongitude);
    $final_message['MsgCode'] = strval(time() . mt_rand(1000, 9999));
    $final_message['vTitle'] = $alertMsg;
    $final_message['iTripId'] = $iCabRequestId_cab_now;
    //$final_message['Time']= strval(date('Y-m-d'));
    if ($eType == "UberX") {
        /*$iVehicleCategoryId=get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId',$selectedCarTypeID,'','true');
        $vVehicleTypeName=get_value('vehicle_type', 'vVehicleType_'.$vLangCode, 'iVehicleTypeId',$selectedCarTypeID,'','true');
        $eFareType=get_value('vehicle_type', 'eFareType', 'iVehicleTypeId',$selectedCarTypeID,'','true');*/
        $sqlv = "SELECT iVehicleCategoryId,vVehicleType_" . $vLangCode . " as vVehicleTypeName,eFareType,ePickStatus,eNightStatus from vehicle_type WHERE iVehicleTypeId = '" . $selectedCarTypeID . "'";
        $tripVehicleData = $obj->MySQLSelect($sqlv);
        $iVehicleCategoryId = $tripVehicleData[0]['iVehicleCategoryId'];
        $vVehicleTypeName = $tripVehicleData[0]['vVehicleTypeName'];
        $eFareType = $tripVehicleData[0]['eFareType'];
        if ($iVehicleCategoryId != 0) {
            $vVehicleCategoryName = get_value('vehicle_category', 'vCategory_' . $vLangCode, 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
            $vVehicleTypeName = $vVehicleCategoryName . "-" . $vVehicleTypeName;
        }
        $final_message['SelectedTypeName'] = $vVehicleTypeName;
        $final_message['eFareType'] = $eFareType;
    } else {
        $final_message['SelectedTypeName'] = "";
        $final_message['eFareType'] = "";
    }

    /*$ePickStatus=get_value('vehicle_type', 'ePickStatus', 'iVehicleTypeId',$selectedCarTypeID,'','true');
    $eNightStatus=get_value('vehicle_type', 'eNightStatus', 'iVehicleTypeId',$selectedCarTypeID,'','true');*/
    $ePickStatus = $tripVehicleData[0]['ePickStatus'];
    $eNightStatus = $tripVehicleData[0]['eNightStatus'];

    $fPickUpPrice = 1;
    $fNightPrice = 1;
    $sourceLocationArr = array($PickUpLatitude, $PickUpLongitude);
    $destinationLocationArr = array($DestLatitude, $DestLongitude);

    $data_flattrip = checkFlatTripnew($sourceLocationArr, $destinationLocationArr, $selectedCarTypeID, $iRentalPackageId);
    /*Changed for rental*/
    $data_surgePrice = checkSurgePrice($selectedCarTypeID, "", $iRentalPackageId);

    if ($data_surgePrice['Action'] == "0") {
        if ($data_surgePrice['message'] == "LBL_PICK_SURGE_NOTE") {
            $fPickUpPrice = $data_surgePrice['SurgePriceValue'];
        } else {
            $fNightPrice = $data_surgePrice['SurgePriceValue'];
        }
    }

    if ($APPLY_SURGE_ON_FLAT_FARE == "No" && $data_flattrip['eFlatTrip'] == "Yes") {
        $fPickUpPrice = 1;
        $fNightPrice = 1;
    }

    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + 60) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
    $sql = "SELECT iGcmRegId,eDeviceType,iDriverId,vLang,tSessionId,iAppVersion FROM register_driver WHERE iDriverId IN (" . $driver_id_auto . ") AND tLocationUpdateDate > '$str_date' AND vAvailability='Available'";
    $result = $obj->MySQLSelect($sql);

    // echo "Res:count:".count($result);exit;
    if (count($result) == 0 || $driver_id_auto == "" || count($Data) == 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "NO_CARS";
        echo json_encode($returnArr);
        exit;
    }

    if ($cashPayment == 'true') {
        $tripPaymentMode = "Cash";
    } else {
        $tripPaymentMode = "Card";
    }

    // $where = " iUserId = '$passengerId'";
    $where = "";

    // $Data_update_passenger['eStatus']=$trip_status;
    $Data_update_passenger['ePayType'] = $tripPaymentMode;

    // if(($generalobj->getConfigurations("configurations","PAYMENT_ENABLED")) == 'Yes'){
    // $Data_update_passenger['vTripPaymentMode']=$tripPaymentMode;
    // }else{
    // $Data_update_passenger['vTripPaymentMode']="Cash";
    // }

    $Data_update_passenger['fTollPrice'] = "0";
    $Data_update_passenger['vTollPriceCurrencyCode'] = "";
    $Data_update_passenger['eTollSkipped'] = "No";

    $Data_update_passenger['iUserId'] = $passengerId;
    $Data_update_passenger['tMsgCode'] = $final_message['MsgCode'];
    $Data_update_passenger['eStatus'] = 'Requesting';
    $Data_update_passenger['vSourceLatitude'] = $PickUpLatitude;
    $Data_update_passenger['vSourceLongitude'] = $PickUpLongitude;

    $Data_update_passenger['vDestLatitude'] = $DestLatitude;
    $Data_update_passenger['vDestLongitude'] = $DestLongitude;
    $Data_update_passenger['tDestAddress'] = $DestAddress;
    $Data_update_passenger['iVehicleTypeId'] = $selectedCarTypeID;
    $Data_update_passenger['fPickUpPrice'] = $fPickUpPrice;
    $Data_update_passenger['fNightPrice'] = $fNightPrice;
    $Data_update_passenger['eType'] = $eType;
    $Data_update_passenger['iPackageTypeId'] = $eType == "Deliver" ? $iPackageTypeId : '';
    $Data_update_passenger['vReceiverName'] = $eType == "Deliver" ? $vReceiverName : '';
    $Data_update_passenger['vReceiverMobile'] = $eType == "Deliver" ? $vReceiverMobile : '';
    $Data_update_passenger['tPickUpIns'] = $eType == "Deliver" ? $tPickUpIns : '';
    $Data_update_passenger['tDeliveryIns'] = $eType == "Deliver" ? $tDeliveryIns : '';
    $Data_update_passenger['tPackageDetails'] = $eType == "Deliver" ? $tPackageDetails : '';
    $Data_update_passenger['vCouponCode'] = $promoCode;
    $Data_update_passenger['iQty'] = $quantity;
    $Data_update_passenger['vRideCountry'] = $vCountryCode;
    $Data_update_passenger['eFemaleDriverRequest'] = $eFemaleDriverRequest;
    $Data_update_passenger['eHandiCapAccessibility'] = $eHandiCapAccessibility;
    $Data_update_passenger['vTimeZone'] = $vTimeZone;
    $Data_update_passenger['dAddedDate'] = date("Y-m-d H:i:s");
    $Data_update_passenger['eFlatTrip'] = $data_flattrip["eFlatTrip"];
    $Data_update_passenger['fFlatTripPrice'] = $data_flattrip["Flatfare"];
    /*added for rental*/
    $Data_update_passenger['iRentalPackageId'] = $iRentalPackageId;

    $insert_id = $obj->MySQLQueryPerform("cab_request_now", $Data_update_passenger, 'insert');
    // $insert_id = mysql_insert_id();

    /*Send Mail to Admin*/
    $tomsg = 'request@onux.org';
    $subjectmsg = 'Solicitud de servicio inmediato';
    $messgaebody = "Administrador,
   		Un proveedor ha recibido una solicitud de servicio inmediata.
			Ingresa al Dashboard/Reports/Service Acceptance Report
			para conocer la actividad de esta solicitud

			Gracias!";

    mail($tomsg, $subjectmsg, $messgaebody);

    $final_message['iCabRequestId'] = $insert_id;
    //$msg_encode  = json_encode($final_message,JSON_UNESCAPED_UNICODE);
    /*$ENABLE_PUBNUB = $generalobj->getConfigurations("configurations","ENABLE_PUBNUB");
    $PUBNUB_DISABLED = $generalobj->getConfigurations("configurations","PUBNUB_DISABLED");
    $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations","PUBNUB_PUBLISH_KEY");
    $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations","PUBNUB_SUBSCRIBE_KEY");*/
    if ($PUBNUB_DISABLED == "Yes") {
        $ENABLE_PUBNUB = "No";
    }
    $alertSendAllowed = true;

    if ($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != "") {

        //$pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);
        $pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY, "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));
        $filter_driver_ids = str_replace(' ', '', $driver_id_auto);
        $driverIds_arr = explode(",", $filter_driver_ids);

        $message = stripslashes(preg_replace("/[\n\r]/", "", $message));

        $deviceTokens_arr_ios = array();
        $registation_ids_new = array();

        $sourceLoc = $PickUpLatitude . ',' . $PickUpLongitude;
        $destLoc = $DestLatitude . ',' . $DestLongitude;
        for ($i = 0; $i < count($driverIds_arr); $i++) {
            /*
            // Add User Request
            $data_userRequest = array();
            $data_userRequest['iUserId'] = $passengerId;
            $data_userRequest['iDriverId'] = $driverIds_arr[$i];
            $data_userRequest['tMessage'] = $msg_encode;
            $data_userRequest['iMsgCode'] = $final_message['MsgCode'];
            $data_userRequest['dAddedDate'] = @date("Y-m-d H:i:s");
            $requestId = addToUserRequest2($data_userRequest);

            // Add Driver Request
            $data_driverRequest = array();
            $data_driverRequest['iDriverId'] = $driverIds_arr[$i];
            $data_driverRequest['iRequestId'] = $requestId;
            $data_driverRequest['iUserId'] = $passengerId;
            $data_driverRequest['iTripId'] = 0;
            $data_driverRequest['vMsgCode'] = $final_message['MsgCode'];
            $data_driverRequest['eStatus'] = "Timeout";
            $data_driverRequest['vStartLatlong'] = $sourceLoc;
            $data_driverRequest['vEndLatlong'] = $destLoc;
            $data_driverRequest['tStartAddress'] = $PickUpAddress;
            $data_driverRequest['tEndAddress'] = $DestAddress ;
            $data_driverRequest['tDate'] = @date("Y-m-d H:i:s");
            addToDriverRequest2($data_driverRequest);   */

            /* For PubNub Setting */
            /*$iAppVersion=get_value("register_driver", 'iAppVersion', "iDriverId",$driverIds_arr[$i],'','true');
            $eDeviceType=get_value("register_driver", 'eDeviceType', "iDriverId",$driverIds_arr[$i],'','true');
            $vDeviceToken=get_value("register_driver", 'iGcmRegId', "iDriverId",$driverIds_arr[$i],'','true');
            $tSessionId=get_value("register_driver", 'tSessionId', "iDriverId",$driverIds_arr[$i],'','true');  */
            $sqld = "SELECT iAppVersion,eDeviceType,iGcmRegId,tSessionId,vLang FROM register_driver WHERE iDriverId = '" . $driverIds_arr[$i] . "'";
            $driverTripData = $obj->MySQLSelect($sqld);
            $iAppVersion = $driverTripData[0]['iAppVersion'];
            $eDeviceType = $driverTripData[0]['eDeviceType'];
            $vDeviceToken = $driverTripData[0]['iGcmRegId'];
            $tSessionId = $driverTripData[0]['tSessionId'];
            $vLang = $driverTripData[0]['vLang'];
            /* For PubNub Setting Finished */

            $final_message['tSessionId'] = $tSessionId;
            //$alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING'," and vCode='".$vLang."'",'true');
            if ($eType == "Ride") {
                $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING', " and vCode='" . $vLang . "'", 'true');
            } elseif ($eType == "UberX") {
                $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_USER_WAITING', " and vCode='" . $vLang . "'", 'true');
            } else {
                $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_DELIVERY_SENDER_WAITING', " and vCode='" . $vLang . "'", 'true');
            }
            // packagename changes
            $sql_request = "SELECT vName_" . $vLang . " as vName FROM package_type WHERE iPackageTypeId='" . $iPackageTypeId . "'";
            $pkgdata = $obj->MySQLSelect($sql_request);

            $final_message['PACKAGE_TYPE'] = $eType == "Deliver" ? $pkgdata[0]['vName'] : '';
            $final_message['vTitle'] = $alertMsg_db;
            $msg_encode_pub = json_encode($final_message, JSON_UNESCAPED_UNICODE);
            $channelName = "CAB_REQUEST_DRIVER_" . $driverIds_arr[$i];
            // $info = $pubnub->publish($channelName, $message);
            $info = $pubnub->publish($channelName, $msg_encode_pub);
            if ($eDeviceType != "Android") {
                array_push($deviceTokens_arr_ios, $vDeviceToken);
            }

        }

    }

    if ($alertSendAllowed == true) {
        $deviceTokens_arr_ios = array();
        $registation_ids_new = array();
        $alertMsg_arr_ios = array();
        $msg_encode_ios = array();
        foreach ($result as $item) {

            //$alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING'," and vCode='".$item['vLang']."'",'true');
            if ($eType == "Ride") {
                $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING', " and vCode='" . $item['vLang'] . "'", 'true');
            } elseif ($eType == "UberX") {
                $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_USER_WAITING', " and vCode='" . $item['vLang'] . "'", 'true');
            } else {
                $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_DELIVERY_SENDER_WAITING', " and vCode='" . $item['vLang'] . "'", 'true');
            }
            $tSessionId = $item['tSessionId'];
            // packagename changes
            $sql_request = "SELECT vName_" . $item['vLang'] . " as vName FROM package_type WHERE iPackageTypeId='" . $iPackageTypeId . "'";
            $pkgdata = $obj->MySQLSelect($sql_request);

            $final_message['PACKAGE_TYPE'] = $eType == "Deliver" ? $pkgdata[0]['vName'] : '';
            $final_message['tSessionId'] = $tSessionId;
            $final_message['vTitle'] = $alertMsg_db;
            $msg_encode = json_encode($final_message, JSON_UNESCAPED_UNICODE);
            if ($item['eDeviceType'] == "Android") {
                array_push($registation_ids_new, $item['iGcmRegId']);
            } else {
                array_push($deviceTokens_arr_ios, $item['iGcmRegId']);
                array_push($alertMsg_arr_ios, $alertMsg_db);
                array_push($msg_encode_ios, $msg_encode);
            }
            // Add User Request
            $data_userRequest = array();
            $data_userRequest['iUserId'] = $passengerId;
            $data_userRequest['iDriverId'] = $item['iDriverId'];
            $data_userRequest['tMessage'] = $msg_encode;
            $data_userRequest['iMsgCode'] = $final_message['MsgCode'];
            $data_userRequest['dAddedDate'] = @date("Y-m-d H:i:s");
            $requestId = addToUserRequest2($data_userRequest);

            // Add Driver Request
            $data_driverRequest = array();
            $data_driverRequest['iDriverId'] = $item['iDriverId'];
            $data_driverRequest['iRequestId'] = $requestId;
            $data_driverRequest['iUserId'] = $passengerId;
            $data_driverRequest['iTripId'] = 0;
            $data_driverRequest['eStatus'] = "Timeout";
            $data_driverRequest['vMsgCode'] = $final_message['MsgCode'];
            $data_driverRequest['vStartLatlong'] = $sourceLoc;
            $data_driverRequest['vEndLatlong'] = $destLoc;
            $data_driverRequest['tStartAddress'] = $PickUpAddress;
            $data_driverRequest['tEndAddress'] = $DestAddress;
            $data_driverRequest['tDate'] = @date("Y-m-d H:i:s");
            addToDriverRequest2($data_driverRequest);
            // addToUserRequest($passengerId,$item['iDriverId'],$msg_encode,$final_message['MsgCode']);
            // addToDriverRequest($item['iDriverId'],$passengerId,0,"Timeout");
        }
        if (count($registation_ids_new) > 0) {
            $final_message['tSessionId'] = "";
            $final_message['vTitle'] = $alertMsg;
            $msg_encode = json_encode($final_message, JSON_UNESCAPED_UNICODE);
            // $Rmessage = array("message" => $message);
            $Rmessage = array("message" => $msg_encode);

            $result = send_notification($registation_ids_new, $Rmessage, 0);

        }
        if (count($deviceTokens_arr_ios) > 0) {
            // sendApplePushNotification(1,$deviceTokens_arr_ios,$msg_encode,$alertMsg,1);
            sendApplePushNotification(1, $deviceTokens_arr_ios, $msg_encode_ios, $alertMsg_arr_ios, 0);
            //sendApplePushNotification(1,$deviceTokens_arr_ios,$msg_encode,$alertMsg,0);
        }
    }

    $tomsg1 = 'pawansandiya@gmail.com';
    $subjectmsg1 = 'Solicitud de servicio programado';
    $messgaebody1 = "Administrador, Un proveedor ha recibido una solicitud de servicio programado. Ingresa al Dashboard/Ride-Job Later Booking para darle el seguimiento adecuado. Gracias!";

    mail($tomsg1, $subjectmsg1, $messgaebody1);

    $returnArr['Action'] = "1";
    echo json_encode($returnArr);

?>