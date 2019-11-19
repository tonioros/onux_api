<?php 
    $iCabBookingId = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $pickUpLocAdd = isset($_REQUEST["pickUpLocAdd"]) ? $_REQUEST["pickUpLocAdd"] : '';
    $pickUpLatitude = isset($_REQUEST["pickUpLatitude"]) ? $_REQUEST["pickUpLatitude"] : '';
    $pickUpLongitude = isset($_REQUEST["pickUpLongitude"]) ? $_REQUEST["pickUpLongitude"] : '';
    $destLocAdd = isset($_REQUEST["destLocAdd"]) ? $_REQUEST["destLocAdd"] : '';
    $destLatitude = isset($_REQUEST["destLatitude"]) ? $_REQUEST["destLatitude"] : '';
    $destLongitude = isset($_REQUEST["destLongitude"]) ? $_REQUEST["destLongitude"] : '';
    $scheduleDate = isset($_REQUEST["scheduleDate"]) ? $_REQUEST["scheduleDate"] : '';
    $iVehicleTypeId = isset($_REQUEST["iVehicleTypeId"]) ? $_REQUEST["iVehicleTypeId"] : '';
    // $timeZone =  isset($_REQUEST["TimeZone"]) ? $_REQUEST["TimeZone"] : '';
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : '';
    $iPackageTypeId = isset($_REQUEST["iPackageTypeId"]) ? $_REQUEST["iPackageTypeId"] : '';
    $vReceiverName = isset($_REQUEST["vReceiverName"]) ? $_REQUEST["vReceiverName"] : '';
    $vReceiverMobile = isset($_REQUEST["vReceiverMobile"]) ? $_REQUEST["vReceiverMobile"] : '';
    $tPickUpIns = isset($_REQUEST["tPickUpIns"]) ? $_REQUEST["tPickUpIns"] : '';
    $tDeliveryIns = isset($_REQUEST["tDeliveryIns"]) ? $_REQUEST["tDeliveryIns"] : '';
    $tPackageDetails = isset($_REQUEST["tPackageDetails"]) ? $_REQUEST["tPackageDetails"] : '';
    $vCouponCode = isset($_REQUEST["PromoCode"]) ? $_REQUEST["PromoCode"] : '';
    $iUserPetId = isset($_REQUEST["iUserPetId"]) ? $_REQUEST["iUserPetId"] : '';
    $cashPayment = isset($_REQUEST["CashPayment"]) ? $_REQUEST["CashPayment"] : '';
    $quantity = isset($_REQUEST["Quantity"]) ? $_REQUEST["Quantity"] : '';
    $fTollPrice = isset($_REQUEST["fTollPrice"]) ? $_REQUEST["fTollPrice"] : '';
    $vTollPriceCurrencyCode = isset($_REQUEST["vTollPriceCurrencyCode"]) ? $_REQUEST["vTollPriceCurrencyCode"] : '';
    $eTollSkipped = isset($_REQUEST["eTollSkipped"]) ? $_REQUEST["eTollSkipped"] : 'Yes';
    $HandicapPrefEnabled = isset($_REQUEST["HandicapPrefEnabled"]) ? $_REQUEST["HandicapPrefEnabled"] : '';
    $PreferFemaleDriverEnable = isset($_REQUEST["PreferFemaleDriverEnable"]) ? $_REQUEST["PreferFemaleDriverEnable"] : '';
    //$eAutoAssign    = 'Yes';
    $iDriverId = isset($_REQUEST["SelectedDriverId"]) ? $_REQUEST["SelectedDriverId"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $iUserAddressId = isset($_REQUEST["iUserAddressId"]) ? $_REQUEST["iUserAddressId"] : '0';
    $tUserComment = isset($_REQUEST["tUserComment"]) ? $_REQUEST["tUserComment"] : '';
    // added for rental
    $iRentalPackageId = isset($_REQUEST["iRentalPackageId"]) ? $_REQUEST["iRentalPackageId"] : '';

    if ($APP_TYPE == "Ride-Delivery-UberX") {
        $sqldata = "SELECT iTripId FROM `trips` WHERE iActive='On Going Trip'  AND iUserId='" . $iUserId . "' AND eType != 'UberX'";
        $TripData = $obj->MySQLSelect($sqldata);
        if (count($TripData) > 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_ONGOING_TRIP_USER_TXT";
            echo json_encode($returnArr);exit;
        }
    }

    $action = ($iCabBookingId != "") ? 'Edit' : 'Add';

    if ($eType == "") {
        $eType = $APP_TYPE == "Delivery" ? "Deliver" : $APP_TYPE;
    }

    // $paymentMode =  isset($_REQUEST["paymentMode"]) ? $_REQUEST["paymentMode"] : 'Cash'; // Cash OR Card
    // $paymentMode = "Cash";
    // $paymentMode = $eType == "Deliver" ?"Card":"Cash";
    if ($cashPayment == 'true') {
        $paymentMode = "Cash";
    } else {
        $paymentMode = "Card";
    }

    checkmemberemailphoneverification($iUserId, "Passenger");
    ## Check Pickup Address For UberX##
    //$APP_TYPE = $generalobj->getConfigurations("configurations","APP_TYPE");
    if ($eType == "UberX") {
        $Data['tUserComment'] = $tUserComment;
        if ($iUserAddressId != "") {
            //$pickUpLocAdd=get_value('user_address', 'vServiceAddress', '    iUserAddressId',$iUserAddressId,'','true');
            $Address = get_value('user_address', 'vAddressType,vBuildingNo,vLandmark,vServiceAddress,vLatitude,vLongitude', '	iUserAddressId', $iUserAddressId, '', '');
            $vAddressType = $Address[0]['vAddressType'];
            $vBuildingNo = $Address[0]['vBuildingNo'];
            $vLandmark = $Address[0]['vLandmark'];
            $vServiceAddress = $Address[0]['vServiceAddress'];
            $pickUpLocAdd = ($vAddressType != "") ? $vAddressType . "\n" : "";
            $pickUpLocAdd .= ($vBuildingNo != "") ? $vBuildingNo . "," : "";
            $pickUpLocAdd .= ($vLandmark != "") ? $vLandmark . "\n" : "";
            $pickUpLocAdd .= ($vServiceAddress != "") ? $vServiceAddress : "";
            $Data['vSourceAddresss'] = $pickUpLocAdd;
            $Data['iUserAddressId'] = $iUserAddressId;
            $pickUpLatitude = $Address[0]['vLatitude'];
            $pickUpLongitude = $Address[0]['vLongitude'];
        } else {
            $Data['vSourceAddresss'] = $pickUpLocAdd;
        }
        $eAutoAssign = 'No';
    } else {
        $Data['vSourceAddresss'] = $pickUpLocAdd;
        $eAutoAssign = 'Yes';
    }

    ### Checking For Pickup And DropOff Disallow ###
    $pickuplocationarr = array($pickUpLatitude, $pickUpLongitude);
    $dropofflocationarr = array($destLatitude, $destLongitude);
    $allowed_ans_pickup = checkAllowedAreaNew($pickuplocationarr, "No");
    if ($allowed_ans_pickup == "No") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_PICKUP_LOCATION_NOT_ALLOW";
        echo json_encode($returnArr);
        exit;
    }

    if ($destLatitude != "" && $destLongitude != "") {
        $allowed_ans_dropoff = checkAllowedAreaNew($dropofflocationarr, "Yes");
        if ($allowed_ans_dropoff == "No") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_DROP_LOCATION_NOT_ALLOW";
            echo json_encode($returnArr);
            exit;
        }
    }
    ### Checking For Pickup And DropOff Disallow ###

    ## Check Pickup Address For UberX##
    ## Check For PichUp/DropOff Location DisAllow ##
    $address_data['PickUpAddress'] = $pickUpLocAdd;
    $address_data['DropOffAddress'] = $destLocAdd;
    if ($destLatitude != "" && $destLongitude != "") {
        $DropOff = "Yes";
    } else {
        $DropOff = "No";
    }
    $DataArr = getOnlineDriverArr($pickUpLatitude, $pickUpLongitude, $address_data, $DropOff, "No", "No", "", $destLatitude, $destLongitude, $eType);
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
    ## Check For PichUp/DropOff Location DisAllow Ends##
    if ($eType == "UberX") {
        $sdate = explode(" ", $scheduleDate);
        $shour = explode("-", $sdate[1]);
        $shour1 = $shour[0];
        $shour2 = $shour[1];
        if ($shour1 == "12" && $shour2 == "01") {
            $shour1 = 00;
        }
        $scheduleDate = $sdate[0] . " " . $shour1 . ":00:00";
        $currentdate = date("Y-m-d H:i:s");
        $datediff = strtotime($scheduleDate) - strtotime($currentdate);
        /*if($datediff < 3600){
    $returnArr['Action'] = "0";
    $returnArr['message'] = "LBL_SCHEDULE_TIME_NOT_AVAILABLE";
    echo json_encode($returnArr);
    exit;
    } */
    }
    $Booking_Date_Time = $scheduleDate;
    $systemTimeZone = date_default_timezone_get();
    // echo "hererrrrr:::".$systemTimeZone;exit;
    $scheduleDate = converToTz($scheduleDate, $systemTimeZone, $vTimeZone);
    // $pickUpDateTime = convertTimeZone("2016-29-14 15:29:41","Asia/Calcutta");

    // date_default_timezone_set($timeZone);
    // echo gmdate('Y-m-d H:i', strtotime($scheduleDate));exit;

    // echo "hererrrrr:::".$pickUpDateTime;exit;
    /*$ePickStatus=get_value('vehicle_type', 'ePickStatus', 'iVehicleTypeId',$iVehicleTypeId,'','true');
    $eNightStatus=get_value('vehicle_type', 'eNightStatus', 'iVehicleTypeId',$iVehicleTypeId,'','true');*/
    $SurchargeDetail = get_value('vehicle_type', 'ePickStatus,eNightStatus', 'iVehicleTypeId', $iVehicleTypeId);
    $ePickStatus = $SurchargeDetail[0]['ePickStatus'];
    $eNightStatus = $SurchargeDetail[0]['eNightStatus'];

    $fPickUpPrice = 1;
    $fNightPrice = 1;

    ## Checking For Flat Trip ##
    $data_flattrip = checkFlatTripnew($pickuplocationarr, $dropofflocationarr, $iVehicleTypeId, $iRentalPackageId);
    $eFlatTrip = $data_flattrip['eFlatTrip'];
    $fFlatTripPrice = $data_flattrip['Flatfare'];
    ## Checking For Flat Trip ##
    $data_surgePrice = checkSurgePrice($iVehicleTypeId, $scheduleDate, $iRentalPackageId);

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

    $Data['fTollPrice'] = "0";
    $Data['vTollPriceCurrencyCode'] = "";
    $Data['eTollSkipped'] = "No";

    $rand_num = rand(10000000, 99999999);

    /*$Booking_Date = @date('d-m-Y',strtotime($scheduleDate));
    $Booking_Time = @date('H:i:s',strtotime($scheduleDate));*/
    $Booking_Date = @date('d-m-Y', strtotime($Booking_Date_Time));
    $Booking_Time = @date('H:i:s', strtotime($Booking_Date_Time));
    $Data['iUserId'] = $iUserId;
    $Data['vSourceLatitude'] = $pickUpLatitude;
    $Data['vSourceLongitude'] = $pickUpLongitude;
    $Data['vDestLatitude'] = $destLatitude;
    $Data['vDestLongitude'] = $destLongitude;
    //$Data['vSourceAddresss']=$pickUpLocAdd;
    $Data['tDestAddress'] = $destLocAdd;
    $Data['ePayType'] = $paymentMode;
    $Data['iVehicleTypeId'] = $iVehicleTypeId;
    $Data['dBooking_date'] = date('Y-m-d H:i', strtotime($scheduleDate));
    $Data['eCancelBy'] = "";
    $Data['fPickUpPrice'] = $fPickUpPrice;
    $Data['fNightPrice'] = $fNightPrice;
    $Data['eType'] = $eType;
    $Data['iUserPetId'] = $iUserPetId;
    $Data['iQty'] = $quantity;
    $Data['vCouponCode'] = $vCouponCode;
    $Data['eAutoAssign'] = $eAutoAssign;
    $Data['vRideCountry'] = $vCountryCode;
    $Data['iDriverId'] = $iDriverId;
    $Data['vTimeZone'] = $vTimeZone;
    $Data['eFemaleDriverRequest'] = $PreferFemaleDriverEnable;
    $Data['eHandiCapAccessibility'] = $HandicapPrefEnabled;
    $Data['eFlatTrip'] = $eFlatTrip;
    $Data['fFlatTripPrice'] = $fFlatTripPrice;
    /*added for rental*/
    $Data['iRentalPackageId'] = $iRentalPackageId;
    if ($eType == "Deliver") {
        $Data['iPackageTypeId'] = $iPackageTypeId;
        $Data['vReceiverName'] = $vReceiverName;
        $Data['vReceiverMobile'] = $vReceiverMobile;
        $Data['tPickUpIns'] = $tPickUpIns;
        $Data['tDeliveryIns'] = $tDeliveryIns;
        $Data['tPackageDetails'] = $tPackageDetails;
    }
    if ($action == "Add") {
        $Data['vBookingNo'] = $rand_num;
        $id = $obj->MySQLQueryPerform("cab_booking", $Data, 'insert');

        /*Send Mail to Admin*/
        $tomsg = 'wirlan@onux.org';
        $subjectmsg = 'Solicitud de servicio programado';
        $messgaebody = "Administrador,
   Un proveedor ha recibido una solicitud de servicio programado.
Ingresa al Dashboard/Ride-Job Later Booking para darle el seguimiento adecuado.

Gracias!";

        mail($tomsg, $subjectmsg, $messgaebody);

    } else {
        $Data['eStatus'] = "Pending";
        $Data['iCancelByUserId'] = "";
        $Data['vCancelReason'] = "";
        $where = " iCabBookingId = '" . $iCabBookingId . "'";
        $id = $obj->MySQLQueryPerform("cab_booking", $Data, 'update', $where);
    }

    if ($id > 0) {
        $returnArr["Action"] = "1";
        if ($eType == "UberX") {
            $returnArr['message'] = "LBL_BOOKING_SUCESS_NOTE";
        } else {
            $returnArr['message'] = $eType == "Deliver" ? "LBL_DELIVERY_BOOKED" : "LBL_RIDE_BOOKED";
        }
        $sql = "SELECT concat(vName,' ',vLastName) as senderName,vEmail,vPhone,vPhoneCode,vLang from  register_user  WHERE iUserId ='" . $iUserId . "'";
        $userdetail = $obj->MySQLSelect($sql);
        $sql = "SELECT concat(vName,' ',vLastName) as drivername,vEmail,vPhone,vcode,iDriverVehicleId,vLang from  register_driver  WHERE iDriverId ='" . $iDriverId . "'";
        $driverdetail = $obj->MySQLSelect($sql);
        $userPhoneNo = $userdetail[0]['vPhone'];
        $userPhoneCode = $userdetail[0]['vPhoneCode'];
        $UserLang = $userdetail[0]['vLang'];
        $DriverPhoneNo = $driverdetail[0]['vPhone'];
        $DriverPhoneCode = $driverdetail[0]['vcode'];
        $DriverLang = $driverdetail[0]['vLang'];
        $Data1['vRider'] = $userdetail[0]['senderName'];
        $Data1['vDriver'] = $driverdetail[0]['drivername'];
        $Data1['vDriverMail'] = $driverdetail[0]['vEmail'];
        $Data1['vRiderMail'] = $userdetail[0]['vEmail'];
        $Data1['vSourceAddresss'] = $pickUpLocAdd;
        //$Data1['tDestAddress']=$destLocAdd;
        //$Data1['dBookingdate']=date('Y-m-d H:i', strtotime($scheduleDate));
        $Data1['dBookingdate'] = date('Y-m-d H:i', strtotime($Booking_Date_Time));
        if ($action == "Add") {
            $Data1['vBookingNo'] = $rand_num;
        } else {
            $BookingNo = get_value('cab_booking', 'vBookingNo', 'iCabBookingId', $iCabBookingId, '', 'true');
            $Data1['vBookingNo'] = $BookingNo;
        }
        $query = "SELECT vLicencePlate FROM driver_vehicle WHERE iDriverVehicleId=" . $iVehicleTypeId;
        $db_driver_vehicles = $obj->MySQLSelect($query);
        if ($eType == "UberX") {
            $sendMailfromDriver = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_DRIVER_APP_SP", $Data1);
        } else {
            $sendMailfromDriver = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_DRIVER_APP", $Data1);
            $sendMailfromUser = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_RIDER_APP", $Data1);
        }
        if ($eType != "UberX") {
            $maildata['DRIVER_NAME'] = $Data1['vDriver'];
            //$maildata['PLATE_NUMBER'] = $db_driver_vehicles[0]['vLicencePlate'];
            $maildata['BOOKING_DATE'] = $Booking_Date;
            $maildata['BOOKING_TIME'] = $Booking_Time;
            $maildata['BOOKING_NUMBER'] = $Data1['vBookingNo'];
            $message_layout = $generalobj->send_messages_user("USER_SEND_MESSAGE_APP", $maildata, "", $UserLang);
            $UsersendMessage = $generalobj->sendUserSMS($userPhoneNo, $userPhoneCode, $message_layout, "");
            if ($UsersendMessage == 0) {
                //$isdCode= $generalobj->getConfigurations("configurations","SITE_ISD_CODE");
                $isdCode = $SITE_ISD_CODE;
                $userPhoneCode = $isdCode;
                $UsersendMessage = $generalobj->sendUserSMS($userPhoneNo, $userPhoneCode, $message_layout, "");
            }
        }
        $maildata1['PASSENGER_NAME'] = $Data1['vRider'];
        $maildata1['BOOKING_DATE'] = $Booking_Date;
        $maildata1['BOOKING_TIME'] = $Booking_Time;
        $maildata1['BOOKING_NUMBER'] = $Data1['vBookingNo'];
        $DRIVER_SMS_TEMPLATE = ($eType == "UberX") ? "DRIVER_SEND_MESSAGE_SP" : "DRIVER_SEND_MESSAGE";
        $message_layout = $generalobj->send_messages_user($DRIVER_SMS_TEMPLATE, $maildata1, "", $DriverLang);
        $DriversendMessage = $generalobj->sendUserSMS($DriverPhoneNo, $DriverPhoneCode, $message_layout, "");
        if ($DriversendMessage == 0) {
            //$isdCode= $generalobj->getConfigurations("configurations","SITE_ISD_CODE");
            $isdCode = $SITE_ISD_CODE;
            $DriverPhoneCode = $isdCode;
            $UsersendMessage = $generalobj->sendUserSMS($DriverPhoneNo, $DriverPhoneCode, $message_layout, "");
        }
    } else {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";

    }

    echo json_encode($returnArr);

?>