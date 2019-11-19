<?php 
    $passenger_id = isset($_REQUEST["PassengerID"]) ? $_REQUEST["PassengerID"] : '';
    $driver_id = isset($_REQUEST["DriverID"]) ? $_REQUEST["DriverID"] : '';
    $iCabRequestId = isset($_REQUEST["iCabRequestId"]) ? $_REQUEST["iCabRequestId"] : '';
    $Source_point_latitude = isset($_REQUEST["start_lat"]) ? $_REQUEST["start_lat"] : '';
    $Source_point_longitude = isset($_REQUEST["start_lon"]) ? $_REQUEST["start_lon"] : '';
    $Source_point_Address = isset($_REQUEST["sAddress"]) ? $_REQUEST["sAddress"] : '';
    $GoogleServerKey = isset($_REQUEST["GoogleServerKey"]) ? $_REQUEST["GoogleServerKey"] : '';
    $iCabBookingId = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : '';
    $vMsgCode = isset($_REQUEST["vMsgCode"]) ? $_REQUEST["vMsgCode"] : '';
    $setCron = isset($_REQUEST["setCron"]) ? $_REQUEST["setCron"] : 'No';
    //$APP_TYPE = $generalobj->getConfigurations("configurations","APP_TYPE");

    $sqldata = "SELECT iTripId FROM `trips` WHERE ( iActive='On Going Trip' OR iActive='Active' ) AND iDriverId='" . $driver_id . "'";
    $TripData = $obj->MySQLSelect($sqldata);
    if (count($TripData) > 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_DRIVER_NOT_ACCEPT_TRIP";
        echo json_encode($returnArr);exit;
    }

    #### Update Driver Request Status of Trip ####
    UpdateDriverRequest2($driver_id, $passenger_id, $iTripId, "", $vMsgCode, "Yes");
    #### Update Driver Request Status of Trip ####

    if ($iCabBookingId != "") {
        $bookingData = get_value('cab_booking', 'iUserId,vSourceLatitude,vSourceLongitude,vSourceAddresss,eType,dBooking_date,eStatus', 'iCabBookingId', $iCabBookingId);
        $passenger_id = $bookingData[0]['iUserId'];
        $Source_point_latitude = $bookingData[0]['vSourceLatitude'];
        $Source_point_longitude = $bookingData[0]['vSourceLongitude'];
        $Source_point_Address = $bookingData[0]['vSourceAddresss'];
        $eType_cabbooking = $bookingData[0]['eType'];
        ## Check Timing For Later Booking ##
        $additional_mins = $BOOKING_LATER_ACCEPT_BEFORE_INTERVAL;
        $additional_mins_into_secs = $additional_mins * 60;
        $dBooking_date = $bookingData[0]['dBooking_date'];
        $currDate = date('Y-m-d H:i:s');
        //$currDate = date("Y-m-d H:i:s", strtotime($currDate . "-".$additional_mins." minutes"));
        $datediff = abs(strtotime($dBooking_date) - strtotime($currDate));
        $eStatusnew = $bookingData[0]['eStatus'];
        if ($datediff > $additional_mins_into_secs) {
            $vDriverLangCode = get_value('register_driver', 'vLang', 'iDriverId', $driver_id, '', 'true');
            $mins = get_value('language_label', 'vValue', 'vLabel', 'LBL_MINUTES_TXT', " and vCode='" . $vDriverLangCode . "'", 'true');
            $hrs = get_value('language_label', 'vValue', 'vLabel', 'LBL_HOURS_TXT', " and vCode='" . $vDriverLangCode . "'", 'true');
            $LBL_RIDE_LATER_START_VALIDATION_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_RIDE_LATER_START_VALIDATION_TXT', " and vCode='" . $vDriverLangCode . "'", 'true');
            if ($additional_mins <= 60) {
                $beforetext = $additional_mins . " " . $mins;
                $message = str_replace('####', $beforetext, $LBL_RIDE_LATER_START_VALIDATION_TXT);
            } else if ($eStatusnew == 'Cancel') {
                $LBL_MANAUL_BOOKING_CANCELLED_MSG = get_value('language_label', 'vValue', 'vLabel', 'LBL_MANAUL_BOOKING_CANCELLED_MSG', " and vCode='" . $vDriverLangCode . "'", 'true');
                $message = $LBL_MANAUL_BOOKING_CANCELLED_MSG;
            } else {
                $hours = floor($additional_mins / 60);
                $beforetext = $hours . " " . $hrs;
                $message = str_replace('####', $beforetext, $LBL_RIDE_LATER_START_VALIDATION_TXT);
            }
            $returnArr['Action'] = "0";
            $returnArr['message'] = $message;
            echo json_encode($returnArr);
            exit;
        }
        ## Check Timing For Later Booking ##
    }

    $DriverMessage = "CabRequestAccepted";

    $TripRideNO = rand(10000000, 99999999);
    $TripVerificationCode = 1234;
    $Active = "Active";

    $vLangCode = get_value('register_user', 'vLang', 'iUserId', $passenger_id, '', 'true');
    if ($vLangCode == "" || $vLangCode == null) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $vGMapLangCode = get_value('language_master', 'vGMapLangCode', 'vCode', $vLangCode, '', 'true');

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
    $tripdriverarrivlbl = $languageLabelsArr['LBL_DRIVER_ARRIVING'];

    /* if($Source_point_Address == ""){
    $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$Source_point_latitude.",".$Source_point_longitude."&key=".$GoogleServerKey."&language=".$vGMapLangCode;

    try {

    $jsonfile = file_get_contents($url);
    $jsondata = json_decode($jsonfile);
    $source_address=$jsondata->results[0]->formatted_address;

    $Source_point_Address = $source_address ;

    } catch (ErrorException $ex) {

    $returnArr['Action'] = "0";
    $returnArr['message']="LBL_TRY_AGAIN_LATER_TXT";
    echo json_encode($returnArr);
    exit;
    }
    }

    if($Source_point_Address == ""){
    $returnArr['Action'] = "0";
    $returnArr['message']="LBL_TRY_AGAIN_LATER_TXT";
    echo json_encode($returnArr);
    exit;
    } */

    $reqestId = "";
    $trip_status_chkField = "iCabRequestId";
    /*added for rental*/
    if ($iCabRequestId != "") {
        $sql = "SELECT eStatus,ePayType,iVehicleTypeId,iCabBookingId,vSourceLatitude,vSourceLongitude,tSourceAddress,vDestLatitude,vDestLongitude,tDestAddress,iRentalPackageId,vCouponCode,eType,iPackageTypeId,vReceiverName,vReceiverMobile,tPickUpIns,tDeliveryIns,tPackageDetails,fPickUpPrice,fNightPrice,iQty,vRideCountry,fTollPrice,vTollPriceCurrencyCode,eTollSkipped,vTimeZone,iUserAddressId,tUserComment,eFlatTrip,fFlatTripPrice FROM cab_request_now WHERE iUserId='$passenger_id' and iCabRequestId = '$iCabRequestId'";
        $check_row = $obj->MySQLSelect($sql);

        $eStatus = $check_row[0]['eStatus'];
        $eType = $check_row[0]['eType'];

        if ($eType_cabbooking != "") {
            $eType = $eType_cabbooking;
        } else {
            $eType = $check_row[0]['eType'];
        }
        $reqestId = $iCabRequestId;
        $trip_status_chkField = "iCabRequestId";
    } else {
        $sql = "select eStatus,eType from cab_booking where iCabBookingId = '$iCabBookingId'";
        $cab_data = $obj->MySQLSelect($sql);
        $eStatus = $cab_data[0]['eStatus'];
        $eType = $cab_data[0]['eType'];
        $reqestId = $iCabBookingId;
        $trip_status_chkField = "iCabBookingId";
    }

    if ($eType == "Ride") {
        $requestcancelbyuser = "LBL_CAR_REQUEST_CANCELLED_TXT";
        $failassigntopassenger = "LBL_FAIL_ASSIGN_TO_PASSENGER_TXT";
        $useronanothertrip = "LBL_USER_ON_ANOTHER_TRIP";
    } elseif ($eType == "Deliver") {
        $requestcancelbyuser = "LBL_CAR_REQUEST_CANCELLED_DELIVERY_TXT";
        $failassigntopassenger = "LBL_FAIL_ASSIGN_TO_PASSENGER_DELIVERY_TXT";
        $useronanothertrip = "LBL_USER_ON_ANOTHER_TRIP";
    } else {
        $requestcancelbyuser = "LBL_CAR_REQUEST_CANCELLED_UFX_TXT";
        $failassigntopassenger = "LBL_FAIL_ASSIGN_TO_PASSENGER_UFX_TXT";
        $useronanothertrip = "LBL_USER_ON_ANOTHER_TRIP";
    }

    if ($eStatus == "Completed") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = $failassigntopassenger;
        echo json_encode($returnArr);
        exit;
    } else {
        if ($APP_TYPE != "UberX") {
            $sql = "select iTripId,vTripStatus from register_user where iUserId='$passenger_id'";
            $user_data = $obj->MySQLSelect($sql);
            $iTripId = $user_data[0]['iTripId'];
            if ($iTripId != "" && $iTripId != 0) {
                $status_trip = get_value("trips", 'iActive', "iTripId", $iTripId, '', 'true');
                $cab_id = get_value("trips", $trip_status_chkField, "iTripId", $iTripId, '', 'true');
                $TripType = get_value("trips", "eType", "iTripId", $iTripId, '', 'true');
                //if(($status_trip == "Active" || $status_trip == "On Going Trip") && $TripType != "UberX"){
                if ($status_trip == "Active" || $status_trip == "On Going Trip") {
                    if ($reqestId == $cab_id) {
                        $returnArr['Action'] = "0";
                        $returnArr['message'] = $failassigntopassenger;
                        echo json_encode($returnArr);
                        exit;
                    } else {
                        $returnArr['Action'] = "0";
                        $returnArr['message'] = "LBL_USER_ON_ANOTHER_TRIP";
                        echo json_encode($returnArr);
                        exit;
                    }
                }
            }
        }
    }
    if ($eStatus == "Requesting" || (($eStatus == "Assign" || $eStatus == "Accepted") && $iCabBookingId != "" && $iCabRequestId == "")) {

        // $eStatus      = $check_row[0]['eStatus'];

        // if ($eStatus == "Requesting") {
        if ($iCabRequestId != "") {
            $where = " iCabRequestId = '$iCabRequestId'";

            $Data_update_cab_request['eStatus'] = 'Complete';

            $obj->MySQLQueryPerform("cab_request_now", $Data_update_cab_request, 'update', $where);
        }
        $sql = "SELECT vCurrencyPassenger,iAppVersion,iUserPetId FROM `register_user` WHERE iUserId = '$passenger_id'";
        $Data_passenger_detail = $obj->MySQLSelect($sql);

        if ($APP_TYPE == "Ride-Delivery-UberX" && $eType == "UberX") {
            $sql = "SELECT iDriverVehicleId FROM `driver_vehicle` WHERE iDriverId = '$driver_id' AND eType = 'UberX'";
            $Data_vehicle_uberx = $obj->MySQLSelect($sql);
            $CAR_id_driver = $Data_vehicle_uberx[0]['iDriverVehicleId'];

            $sql = "SELECT iDriverVehicleId,vCurrencyDriver,iAppVersion,vName,vLastName FROM `register_driver` WHERE iDriverId = '$driver_id'";
            $Data_vehicle = $obj->MySQLSelect($sql);
        } else {
            $sql = "SELECT iDriverVehicleId,vCurrencyDriver,iAppVersion,vName,vLastName FROM `register_driver` WHERE iDriverId = '$driver_id'";
            $Data_vehicle = $obj->MySQLSelect($sql);
            $CAR_id_driver = $Data_vehicle[0]['iDriverVehicleId'];
        }
        // Changed for rental
        if ($iCabBookingId != "") {
            $sql_booking = "SELECT vSourceLatitude, vSourceLongitude,vSourceAddresss,vDestLatitude,vDestLongitude,tDestAddress,ePayType,iVehicleTypeId,iRentalPackageId,eType,iPackageTypeId,vReceiverName,vReceiverMobile,tPickUpIns,tDeliveryIns,tPackageDetails,fPickUpPrice,fNightPrice,iUserPetId,vCouponCode,iQty,vRideCountry,fTollPrice,vTollPriceCurrencyCode,eTollSkipped, vTimeZone,iUserAddressId,tUserComment,eFlatTrip,fFlatTripPrice FROM cab_booking WHERE iCabBookingId='$iCabBookingId'";

            $data_booking = $obj->MySQLSelect($sql_booking);

            $iSelectedCarType = $data_booking[0]['iVehicleTypeId'];
            $iRentalPackageId = $data_booking[0]['iRentalPackageId'];
            $vTripPaymentMode = $data_booking[0]['ePayType'];
            $tDestinationLatitude = $data_booking[0]['vDestLatitude'];
            $tDestinationLongitude = $data_booking[0]['vDestLongitude'];
            $tDestinationAddress = $data_booking[0]['tDestAddress'];
            $fPickUpPrice = $data_booking[0]['fPickUpPrice'];
            $fNightPrice = $data_booking[0]['fNightPrice'];
            $Source_point_latitude = $data_booking[0]['vSourceLatitude'];
            $Source_point_longitude = $data_booking[0]['vSourceLongitude'];
            $Source_point_Address = $data_booking[0]['vSourceAddresss'];

            $eType = $data_booking[0]['eType'];
            $iPackageTypeId = $data_booking[0]['iPackageTypeId'];
            $vReceiverName = $data_booking[0]['vReceiverName'];
            $vReceiverMobile = $data_booking[0]['vReceiverMobile'];
            $tPickUpIns = $data_booking[0]['tPickUpIns'];
            $tDeliveryIns = $data_booking[0]['tDeliveryIns'];
            $tPackageDetails = $data_booking[0]['tPackageDetails'];
            $iUserPetId = $data_booking[0]['iUserPetId'];
            $vCouponCode = $data_booking[0]['vCouponCode'];
            $iQty = $data_booking[0]['iQty'];
            $vRideCountry = $data_booking[0]['vRideCountry'];
            $fTollPrice = $data_booking[0]['fTollPrice'];
            $vTollPriceCurrencyCode = $data_booking[0]['vTollPriceCurrencyCode'];
            $eTollSkipped = $data_booking[0]['eTollSkipped'];
            $vTimeZone = $data_booking[0]['vTimeZone'];
            $iUserAddressId = $data_booking[0]['iUserAddressId'];
            $tUserComment = $data_booking[0]['tUserComment'];
            $eFlatTrip = $data_booking[0]['eFlatTrip'];
            $fFlatTripPrice = $data_booking[0]['fFlatTripPrice'];
        } else {
            $iSelectedCarType = $check_row[0]['iVehicleTypeId'];
            $iRentalPackageId = $check_row[0]['iRentalPackageId'];
            $vTripPaymentMode = $check_row[0]['ePayType'];
            $tDestinationLatitude = $check_row[0]['vDestLatitude'];
            $tDestinationLongitude = $check_row[0]['vDestLongitude'];
            $tDestinationAddress = $check_row[0]['tDestAddress'];
            $fPickUpPrice = $check_row[0]['fPickUpPrice'];
            $fNightPrice = $check_row[0]['fNightPrice'];
            $Source_point_latitude = $check_row[0]['vSourceLatitude'];
            $Source_point_longitude = $check_row[0]['vSourceLongitude'];
            $Source_point_Address = $check_row[0]['tSourceAddress'];

            $eType = $check_row[0]['eType'];
            $iPackageTypeId = $check_row[0]['iPackageTypeId'];
            $vReceiverName = $check_row[0]['vReceiverName'];
            $vReceiverMobile = $check_row[0]['vReceiverMobile'];
            $tPickUpIns = $check_row[0]['tPickUpIns'];
            $tDeliveryIns = $check_row[0]['tDeliveryIns'];
            $tPackageDetails = $check_row[0]['tPackageDetails'];
            $iUserPetId = $Data_passenger_detail[0]['iUserPetId'];
            $vCouponCode = $check_row[0]['vCouponCode'];
            $iQty = $check_row[0]['iQty'];
            $eFlatTrip = $check_row[0]['eFlatTrip'];
            $fFlatTripPrice = $check_row[0]['fFlatTripPrice'];
            $vRideCountry = $check_row[0]['vRideCountry'];
            $fTollPrice = $check_row[0]['fTollPrice'];
            $vTollPriceCurrencyCode = $check_row[0]['vTollPriceCurrencyCode'];
            $eTollSkipped = $check_row[0]['eTollSkipped'];
            $vTimeZone = $check_row[0]['vTimeZone'];
            $iUserAddressId = $check_row[0]['iUserAddressId'];
            $tUserComment = $check_row[0]['tUserComment'];
            $iCabBookingId = $check_row[0]['iCabBookingId'];
        }

        /*     if($vRideCountry != "") {
        $newTimeZone = get_value('country', 'vTimeZone', 'LOWER(vCountry)', strtolower($vRideCountry),'',true);
        //$newTimeZone = $
        @date_default_timezone_set($newTimeZone);
        }
         */
        $Data_trips['vRideNo'] = $TripRideNO;
        $Data_trips['iUserId'] = $passenger_id;
        $Data_trips['iDriverId'] = $driver_id;
        $Data_trips['tTripRequestDate'] = @date("Y-m-d H:i:s");
        $Data_trips['tStartLat'] = $Source_point_latitude;
        $Data_trips['tStartLong'] = $Source_point_longitude;
        $Data_trips['tSaddress'] = $Source_point_Address;
        $Data_trips['iActive'] = $Active;
        $Data_trips['iDriverVehicleId'] = $CAR_id_driver;
        $Data_trips['iVerificationCode'] = $TripVerificationCode;
        $Data_trips['iVehicleTypeId'] = $iSelectedCarType;
        $Data_trips['iRentalPackageId'] = $iRentalPackageId;
        /*$Data_trips['eFareType'] = get_value('vehicle_type', 'eFareType', 'iVehicleTypeId', $iSelectedCarType,'','true');
        $Data_trips['fVisitFee'] = get_value('vehicle_type', 'fVisitFee', 'iVehicleTypeId', $iSelectedCarType,'','true');  */
        $VehicleData = get_value('vehicle_type', 'eFareType,fVisitFee,eIconType,iWaitingFeeTimeLimit', 'iVehicleTypeId', $iSelectedCarType);
        $Data_trips['eFareType'] = $VehicleData[0]['eFareType'];
        $Data_trips['fVisitFee'] = $VehicleData[0]['fVisitFee'];
        $Data_trips['iWaitingFeeTimeLimit'] = $VehicleData[0]['iWaitingFeeTimeLimit'];
        $Data_trips['vTripPaymentMode'] = $vTripPaymentMode;
        $Data_trips['tEndLat'] = $tDestinationLatitude;
        $Data_trips['tEndLong'] = $tDestinationLongitude;
        $Data_trips['tDaddress'] = $tDestinationAddress;
        $Data_trips['fPickUpPrice'] = $fPickUpPrice;
        $Data_trips['fNightPrice'] = $fNightPrice;
        $Data_trips['iQty'] = $iQty;

        $Data_trips['eType'] = $eType;
        $Data_trips['iPackageTypeId'] = $iPackageTypeId;
        $Data_trips['vReceiverName'] = $vReceiverName;
        $Data_trips['vReceiverMobile'] = $vReceiverMobile;
        $Data_trips['tPickUpIns'] = $tPickUpIns;
        $Data_trips['tDeliveryIns'] = $tDeliveryIns;
        $Data_trips['tPackageDetails'] = $tPackageDetails;
        $Data_trips['iUserPetId'] = $iUserPetId;
        $Data_trips['vCountryUnitRider'] = getMemberCountryUnit($passenger_id, "Passenger");
        $Data_trips['vCountryUnitDriver'] = getMemberCountryUnit($driver_id, "Driver");
        $Data_trips['fTollPrice'] = $fTollPrice;
        $Data_trips['vTollPriceCurrencyCode'] = $vTollPriceCurrencyCode;
        $Data_trips['eTollSkipped'] = $eTollSkipped;
        $Data_trips['vTimeZone'] = $vTimeZone;
        $Data_trips['iUserAddressId'] = $iUserAddressId;
        $Data_trips['tUserComment'] = $tUserComment;
        $Data_trips['iCabBookingId'] = $iCabBookingId;
        $Data_trips['iCabRequestId'] = $iCabRequestId;
        $Data_trips['eFlatTrip'] = $eFlatTrip;
        $Data_trips['fFlatTripPrice'] = $fFlatTripPrice;
        //$eIconType = get_value('vehicle_type', 'eIconType', 'iVehicleTypeId', $iSelectedCarType,'','true');
        $eIconType = $VehicleData[0]['eIconType'];
        // PAatch ID - WP101
        //if($APP_TYPE == "UberX"){
        if ($eType == "UberX") {
            $iVehicleCategoryId = get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId', $iSelectedCarType, '', 'true');
            $imageuploaddata = get_value('vehicle_category', 'eBeforeUpload, eAfterUpload', 'iVehicleCategoryId', $iVehicleCategoryId);
            $Data_trips['eBeforeUpload'] = $imageuploaddata[0]['eBeforeUpload'];
            $Data_trips['eAfterUpload'] = $imageuploaddata[0]['eAfterUpload'];
        }

        if ($vCouponCode != '') {
            $Data_trips['vCouponCode'] = $vCouponCode;

            $noOfCouponUsed = get_value('coupon', 'iUsed', 'vCouponCode', $vCouponCode, '', 'true');
            $where = " vCouponCode = '" . $vCouponCode . "'";
            $data_coupon['iUsed'] = $noOfCouponUsed + 1;
            $obj->MySQLQueryPerform("coupon", $data_coupon, 'update', $where);
        }

        $currencyList = get_value('currency', '*', 'eStatus', 'Active');

        for ($i = 0; $i < count($currencyList); $i++) {
            $currencyCode = $currencyList[$i]['vName'];
            $Data_trips['fRatio_' . $currencyCode] = $currencyList[$i]['Ratio'];
        }

        $Data_trips['vCurrencyPassenger'] = $Data_passenger_detail[0]['vCurrencyPassenger'];
        $Data_trips['vCurrencyDriver'] = $Data_vehicle[0]['vCurrencyDriver'];
        // $Data_trips['fRatioPassenger']=($obj->MySQLSelect("SELECT Ratio FROM currency WHERE vName='".$check_row[0]['vCurrencyPassenger']."' ")[0]['Ratio']);
        $Data_trips['fRatioPassenger'] = get_value('currency', 'Ratio', 'vName', $Data_passenger_detail[0]['vCurrencyPassenger'], '', 'true');
        // $Data_trips['fRatioDriver']=($obj->MySQLSelect("SELECT Ratio FROM currency WHERE vName='".$Data_vehicle[0]['vCurrencyDriver']."' ")[0]['Ratio']);
        $Data_trips['fRatioDriver'] = get_value('currency', 'Ratio', 'vName', $Data_vehicle[0]['vCurrencyDriver'], '', 'true');

        $id = $obj->MySQLQueryPerform("trips", $Data_trips, 'insert');
        $iTripId = $id;
        $trip_status = "Active";

        if ($iCabRequestId != "") {
            $where1 = " iCabRequestId = '$iCabRequestId'";
            $Data_update_cab_request['iTripId'] = $iTripId;
            $Data_update_cab_request['iDriverId'] = $driver_id;
            $obj->MySQLQueryPerform("cab_request_now", $Data_update_cab_request, 'update', $where1);
        }
        #### Update Driver Request Status of Trip ####
        UpdateDriverRequest2($driver_id, $passenger_id, $iTripId, "Accept", $vMsgCode, "No");
        #### Update Driver Request Status of Trip ####

        if ($iCabBookingId > 0) {
            $where = " iCabBookingId = '$iCabBookingId'";
            $data_update_booking['iTripId'] = $iTripId;
            $data_update_booking['eStatus'] = "Completed";
            $data_update_booking['iDriverId'] = $driver_id;
            $obj->MySQLQueryPerform("cab_booking", $data_update_booking, 'update', $where);
        }

        $where = " iUserId = '$passenger_id'";
        $Data_update_passenger['iTripId'] = $iTripId;
        $Data_update_passenger['vTripStatus'] = $trip_status;
        $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);

        $where = " iDriverId = '$driver_id'";
        $Data_update_driver['iTripId'] = $iTripId;
        $Data_update_driver['vTripStatus'] = $trip_status;
        $Data_update_driver['vRideCountry'] = $vRideCountry;
        $Data_update_driver['vAvailability'] = "Not Available";
        $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

        if ($eType == "Deliver") {
            $drivername = $Data_vehicle[0]['vName'] . " " . $Data_vehicle[0]['vLastName'];
            $tripdriverarrivlbl = $languageLabelsArr['LBL_CARRIER'] . " " . $drivername . " " . $languageLabelsArr['LBL_DRIVER_IS_ARRIVING'];
            $alertMsg = $tripdriverarrivlbl;
        } elseif ($eType == "Ride") {
            $alertMsg = $tripdriverarrivlbl;
        } else {
            $drivername = $Data_vehicle[0]['vName'] . " " . $Data_vehicle[0]['vLastName'];
            $tripdriverarrivlbl = $languageLabelsArr['LBL_PROVIDER'] . " " . $drivername . " " . $languageLabelsArr['LBL_DRIVER_IS_ARRIVING'];
            $alertMsg = $tripdriverarrivlbl;
        }

        $message_arr = array();
        $message_arr['iDriverId'] = $driver_id;
        $message_arr['Message'] = $DriverMessage;
        $message_arr['iTripId'] = strval($iTripId);
        $message_arr['DriverAppVersion'] = strval($Data_vehicle[0]['iAppVersion']);
        if ($iCabBookingId > 0) {
            $message_arr['iCabBookingId'] = $iCabBookingId;
            $message_arr['iBookingId'] = $iCabBookingId;
        }
        $message_arr['eType'] = $eType;
        $message_arr['iTripVerificationCode'] = $TripVerificationCode;
        $message_arr['driverName'] = $Data_vehicle[0]['vName'] . " " . $Data_vehicle[0]['vLastName'];
        $message_arr['vRideNo'] = $TripRideNO;
        $message_arr['vTitle'] = $alertMsg;

        $message = json_encode($message_arr);

        #####################Add Status Message#########################
        $DataTripMessages['tMessage'] = $message;
        $DataTripMessages['iDriverId'] = $driver_id;
        $DataTripMessages['iTripId'] = $iTripId;
        $DataTripMessages['iUserId'] = $passenger_id;
        $DataTripMessages['eFromUserType'] = "Driver";
        $DataTripMessages['eToUserType'] = "Passenger";
        $DataTripMessages['eReceived'] = "No";
        $DataTripMessages['dAddedDate'] = @date("Y-m-d H:i:s");

        $obj->MySQLQueryPerform("trip_status_messages", $DataTripMessages, 'insert');
        ################################################################

        if ($setCron == 'Yes') {
            $passengerDetail = get_value('register_user', 'vName,vLastName,vPhone,vPhoneCode', 'iUserId', $passenger_id);
            $passengerName = $passengerDetail[0]['vName'] . ' ' . $passengerDetail[0]['vLastName'];
            $vPhoneCode = $passengerDetail[0]['vPhoneCode'];
            $vPhone = $passengerDetail[0]['vPhone'];
            $driverName = $Data_vehicle[0]['vName'] . ' ' . $Data_vehicle[0]['vLastName'];
            $messageEmail['details'] = '<p>Dear Administrator,</p>
				<p>Driver ( ' . $driverName . ' ) is assigned successfully for the following manual booking.</p>
				<p>Name: ' . $passengerName . ',</p>
				<p>Contact Number: +' . $vPhoneCode . $vPhone . '</p>';
            $mail = $generalobj->send_email_user('CRON_BOOKING_EMAIL', $messageEmail);
            $where_cabid2 = " iCabBookingId = '" . $iCabBookingId . "'";
            $Data_update2['eAssigned'] = 'Yes';
            $Data_update2['iDriverId'] = $driver_id;
            $id = $obj->MySQLQueryPerform("cab_booking", $Data_update2, 'update', $where_cabid2);
        }

        if ($iTripId > 0) {
            /*$ENABLE_PUBNUB = $generalobj->getConfigurations("configurations","ENABLE_PUBNUB");
            $PUBNUB_DISABLED = $generalobj->getConfigurations("configurations","PUBNUB_DISABLED");
            $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations","PUBNUB_PUBLISH_KEY");
            $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations","PUBNUB_SUBSCRIBE_KEY");*/
            if ($PUBNUB_DISABLED == "Yes") {
                $ENABLE_PUBNUB = "No";
            }

            $alertSendAllowed = true;

            /* For PubNub Setting */
            $tableName = "register_user";
            $iMemberId_VALUE = $passenger_id;
            $iMemberId_KEY = "iUserId";
            /*$iAppVersion=get_value($tableName, 'iAppVersion', $iMemberId_KEY,$iMemberId_VALUE,'','true');
            $eDeviceType=get_value($tableName, 'eDeviceType', $iMemberId_KEY,$iMemberId_VALUE,'','true');*/
            $AppData = get_value($tableName, 'iAppVersion,eDeviceType', $iMemberId_KEY, $iMemberId_VALUE);
            $iAppVersion = $AppData[0]['iAppVersion'];
            $eDeviceType = $AppData[0]['eDeviceType'];
            /* For PubNub Setting Finished */

            $sql = "SELECT iGcmRegId,eDeviceType FROM register_user WHERE iUserId='$passenger_id'";
            $result = $obj->MySQLSelect($sql);
            $registatoin_ids = $result[0]['iGcmRegId'];
            $deviceTokens_arr_ios = array();
            $registation_ids_new = array();

            if ($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != "") {
                //$pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);
                $pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY, "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));
                $channelName = "PASSENGER_" . $passenger_id;

                $tSessionId = get_value("register_user", 'tSessionId', "iUserId", $passenger_id, '', 'true');
                $message_arr['tSessionId'] = $tSessionId;
                $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

                $info = $pubnub->publish($channelName, $message_pub);
                if ($result[0]['eDeviceType'] != "Android") {
                    //$alertMsg = "Driver is arriving";
                    //$alertMsg = $tripdriverarrivlbl;
                    array_push($deviceTokens_arr_ios, $result[0]['iGcmRegId']);
                    // sendApplePushNotification(0,$deviceTokens_arr_ios,"",$alertMsg,0);
                }
            } else {
                $alertSendAllowed = true;
            }
            if ($alertSendAllowed == true) {
                if ($result[0]['eDeviceType'] == "Android") {
                    array_push($registation_ids_new, $result[0]['iGcmRegId']);
                    $Rmessage = array("message" => $message);
                    $result = send_notification($registation_ids_new, $Rmessage, 0);
                } else {
                    //$alertMsg = "Driver is arriving";
                    //$alertMsg = $tripdriverarrivlbl;
                    array_push($deviceTokens_arr_ios, $result[0]['iGcmRegId']);
                    sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
                }
            }

            $returnArr['Action'] = "1";
            $data['iTripId'] = $iTripId;
            $data['tEndLat'] = $tDestinationLatitude;
            $data['tEndLong'] = $tDestinationLongitude;
            $data['tDaddress'] = $tDestinationAddress;
            $data['PAppVersion'] = $Data_passenger_detail[0]['iAppVersion'];
            $data['eFareType'] = $Data_trips['eFareType'];
            $data['vVehicleType'] = $eIconType;
            //$returnArr['APP_TYPE'] = $generalobj->getConfigurations("configurations","APP_TYPE");
            $returnArr['APP_TYPE'] = $APP_TYPE;
            $returnArr['message'] = $data;

            if ($iCabBookingId != "") {
                $passengerData = get_value('register_user', 'vName,vLastName,vImgName,vFbId,vAvgRating,vPhone,vPhoneCode,iAppVersion', 'iUserId', $passenger_id);
                $returnArr['sourceLatitude'] = $Source_point_latitude;
                $returnArr['sourceLongitude'] = $Source_point_longitude;
                $returnArr['PassengerId'] = $passenger_id;
                $returnArr['PName'] = $passengerData[0]['vName'] . ' ' . $passengerData[0]['vLastName'];
                $returnArr['PPicName'] = $passengerData[0]['vImgName'];
                $returnArr['PFId'] = $passengerData[0]['vFbId'];
                $returnArr['PRating'] = $passengerData[0]['vAvgRating'];
                $returnArr['PPhone'] = $passengerData[0]['vPhone'];
                $returnArr['PPhoneC'] = $passengerData[0]['vPhoneCode'];
                $returnArr['PAppVersion'] = $passengerData[0]['iAppVersion'];
                $returnArr['TripId'] = strval($iTripId);
                $returnArr['DestLocLatitude'] = $tDestinationLatitude;
                $returnArr['DestLocLongitude'] = $tDestinationLongitude;
                $returnArr['DestLocAddress'] = $tDestinationAddress;
                $returnArr['vVehicleType'] = $eIconType;
            }
            echo json_encode($returnArr);exit;
        } else {
            $data['Action'] = "0";
            $data['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            echo json_encode($data);
            exit;
        }

        /* }else{
    $returnArr['Action'] = "0";
    $returnArr['message']="LBL_CAR_REQUEST_CANCELLED_TXT";
    echo json_encode($returnArr);
    } */
    } else {
        if ($eStatus == "Complete") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = $failassigntopassenger;
        } else if ($eStatus == "Cancel") {
            $returnArr['Action'] = "0";
            $vDriverLangCode = get_value('register_driver', 'vLang', 'iDriverId', $driver_id, '', 'true');
            $LBL_MANAUL_BOOKING_CANCELLED_MSG = get_value('language_label', 'vValue', 'vLabel', 'LBL_MANAUL_BOOKING_CANCELLED_MSG', " and vCode='" . $vDriverLangCode . "'", 'true');
            $returnArr['message'] = $LBL_MANAUL_BOOKING_CANCELLED_MSG;
            echo json_encode($returnArr);
            exit;
        } else if ($eStatus == "Cancelled") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = $requestcancelbyuser;
        }
        echo json_encode($returnArr);
    }

?>