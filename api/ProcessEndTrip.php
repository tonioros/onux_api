<?php 

    global $generalobj;
    $tripId = isset($_REQUEST["TripId"]) ? $_REQUEST["TripId"] : '';
    $userId = isset($_REQUEST["PassengerId"]) ? $_REQUEST["PassengerId"] : '';
    $driverId = isset($_REQUEST["DriverId"]) ? $_REQUEST["DriverId"] : '';
    $latitudes = isset($_REQUEST["latList"]) ? $_REQUEST["latList"] : '';
    $longitudes = isset($_REQUEST["lonList"]) ? $_REQUEST["lonList"] : '';
    $tripDistance = isset($_REQUEST["TripDistance"]) ? $_REQUEST["TripDistance"] : '0';
    $dAddress = isset($_REQUEST["dAddress"]) ? $_REQUEST["dAddress"] : '';
    // $currentCity= isset($_REQUEST["currentCity"]) ? $_REQUEST["currentCity"] : '';
    $destination_lat = isset($_REQUEST["dest_lat"]) ? $_REQUEST["dest_lat"] : '';
    $destination_lon = isset($_REQUEST["dest_lon"]) ? $_REQUEST["dest_lon"] : '';
    $isTripCanceled = isset($_REQUEST["isTripCanceled"]) ? $_REQUEST["isTripCanceled"] : '';
    $driverComment = isset($_REQUEST["Comment"]) ? $_REQUEST["Comment"] : '';
    $driverReason = isset($_REQUEST["Reason"]) ? $_REQUEST["Reason"] : '';
    $image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
    $fMaterialFee = isset($_REQUEST["fMaterialFee"]) ? $_REQUEST["fMaterialFee"] : '';
    $fMiscFee = isset($_REQUEST["fMiscFee"]) ? $_REQUEST["fMiscFee"] : '';
    $fDriverDiscount = isset($_REQUEST["fDriverDiscount"]) ? $_REQUEST["fDriverDiscount"] : '';
    $vCurrencyDriver = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $driverId, '', 'true');
    $DriverRation = get_value('currency', 'Ratio', 'vName', $vCurrencyDriver, '', 'true');

    if ($image_object) {
        ExifCleaning::adjustImageOrientation($image_object);
    }
    if ($userId == 32340) {
        $destination_lat = '31.409562';
        $destination_lon = '74.261048';
    }
    //$exifDATA = exif_read_data($image_object, 0, true);
    //echo "EXIFData::<BR/>";
    //print_r($exifDATA);exit;

    //$currencyRatio = get_value('currency', 'Ratio', 'eDefault', 'Yes','','true');
    $fMaterialFee = round($fMaterialFee / $DriverRation, 2);
    $fMiscFee = round($fMiscFee / $DriverRation, 2);
    $fDriverDiscount = round($fDriverDiscount / $DriverRation, 2);
    $eType = get_value('trips', 'eType', 'iTripId', $tripId, '', 'true');

    $Active = "Finished";
    $vLangCode = get_value('register_user', 'vLang', 'iUserId', $userId, '', 'true');
    if ($vLangCode == "" || $vLangCode == null) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    ### Checking For Fixlocation Trip ###
    /*$sqlt = "SELECT tStartLat,tStartLong,eFlatTrip,iVehicleTypeId FROM trips WHERE iTripId = '".$tripId."'";
    $flattrip = $obj->MySQLSelect($sqlt);
    $FlatTrip = $flattrip[0]['eFlatTrip'];
    if($FlatTrip == "Yes"){
    $pickuplocationarr_flattrip = array($flattrip[0]['tStartLat'],$flattrip[0]['tStartLong']);
    $dropofflocationarr_flattrip = array($destination_lat,$destination_lon);
    $data_flattrip_check = checkFlatTripnew($pickuplocationarr_flattrip,$dropofflocationarr_flattrip,$flattrip[0]['iVehicleTypeId']);
    $EndFlatTrip = $data_flattrip_check['eFlatTrip'];
    if($EndFlatTrip == "No"){
    $wheretrip = " iTripId = '" . $tripId . "'";
    $Data_update_flattrips['eFlatTrip'] = "No";
    $Data_update_flattrips['fFlatTripPrice'] = 0;
    $Flat_Trip_id = $obj->MySQLQueryPerform("trips",$Data_update_flattrips,'update',$wheretrip);
    }
    }   */
    ### Checking For Fixlocation Trip ###

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
    $tripcancelbydriver = $languageLabelsArr['LBL_TRIP_CANCEL_BY_DRIVER'];
    $tripfinish = $languageLabelsArr['LBL_DRIVER_END_NOTIMSG'];
    $tripfinish_ride = $languageLabelsArr['LBL_TRIP_FINISH'];
    $tripfinish_delivery = $languageLabelsArr['LBL_DELIVERY_FINISH'];

    $message_arr = array();
    $message_arr['ShowTripFare'] = "true";
    if ($isTripCanceled == "true") {
        $message = "TripCancelledByDriver";
    } else {
        $message = "TripEnd";
    }

    $sql = "SELECT CONCAT(rd.vName,' ',rd.vLastName) AS driverName,tr.vRideNo FROM trips as tr,register_driver as rd WHERE tr.iTripId=rd.iTripId AND rd.iDriverId = '" . $driverId . "'";
    $result22 = $obj->MySQLSelect($sql);

    if ($isTripCanceled == "true") {
        // $alertMsg = $tripcancelbydriver;
        if ($eType == "UberX") {
            $usercanceltriplabel = $result22[0]['driverName'] . ':' . $result22[0]['vRideNo'] . '-' . $languageLabelsArr['LBL_PREFIX_JOB_CANCEL_DRIVER'] . ' ' . $driverReason;
        } elseif ($eType == "Ride") {
            $usercanceltriplabel = $languageLabelsArr['LBL_PREFIX_TRIP_CANCEL_DRIVER'] . ' ' . $driverReason;
        } else {
            $usercanceltriplabel = $languageLabelsArr['LBL_PREFIX_DELIVERY_CANCEL_DRIVER'] . ' ' . $driverReason;
        }
        $alertMsg = $usercanceltriplabel;
    } else {
        if ($eType == "UberX") {
            //$alertMsg = $tripfinish;
            $alertMsg = $result22[0]['driverName'] . " " . $tripfinish . " " . $result22[0]['vRideNo'];
        } elseif ($eType == "Ride") {
            $alertMsg = $tripfinish_ride;
        } else {
            $alertMsg = $tripfinish_delivery;
        }
    }
    $message_arr['Message'] = $message;
    $message_arr['iTripId'] = $tripId;
    $message_arr['iDriverId'] = $driverId;
    $message_arr['driverName'] = $result22[0]['driverName'];
    $message_arr['vRideNo'] = $result22[0]['vRideNo'];
    if ($isTripCanceled == "true") {
        $message_arr['Reason'] = $driverReason;
        $message_arr['isTripStarted'] = "true";
    }
    $message_arr['vTitle'] = $alertMsg;
    $message_arr['eType'] = $eType;

    $message = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

    #####################Add Status Message#########################
    $DataTripMessages['tMessage'] = $message;
    $DataTripMessages['iDriverId'] = $driverId;
    $DataTripMessages['iTripId'] = $tripId;
    $DataTripMessages['iUserId'] = $userId;
    $DataTripMessages['eFromUserType'] = "Driver";
    $DataTripMessages['eToUserType'] = "Passenger";
    $DataTripMessages['eReceived'] = "No";
    $DataTripMessages['dAddedDate'] = @date("Y-m-d H:i:s");

    $obj->MySQLQueryPerform("trip_status_messages", $DataTripMessages, 'insert');
    ################################################################

    $couponCode = get_value('trips', 'vCouponCode', 'iTripId', $tripId, '', 'true');
    $discountValue = 0;
    $discountValueType = "cash";
    if ($couponCode != '') {
        /*$discountValue = get_value('coupon', 'fDiscount', 'vCouponCode', $couponCode,'','true');
        $discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode,'','true');*/
        $CouponData = get_value('coupon', 'fDiscount,eType', 'vCouponCode', $couponCode);
        $discountValue = $CouponData[0]['fDiscount'];
        $discountValueType = $CouponData[0]['eType'];
    }

    if ($latitudes != '' && $longitudes != '' && $eType != "UberX") {
        processTripsLocations($tripId, $latitudes, $longitudes);
    }

    $vCurrencyDriver = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $driverId, '', 'true');
    $currencySymbolDriver = get_value('currency', 'vSymbol', 'vName', $vCurrencyDriver, '', 'true');

    $sql = "SELECT tStartDate,tEndDate,tDriverArrivedDate,iVehicleTypeId,tStartLat,tStartLong,eFareType,fRatio_" . $vCurrencyDriver . " as fRatioDriver, vTripPaymentMode,fPickUpPrice,fNightPrice, eType, fTollPrice,eFlatTrip,fFlatTripPrice,eHailTrip FROM trips WHERE iTripId='$tripId'";
    $trip_start_data_arr = $obj->MySQLSelect($sql);

    $tripDistance = calcluateTripDistance($tripId);

    $sourcePointLatitude = $trip_start_data_arr[0]['tStartLat'];
    $sourcePointLongitude = $trip_start_data_arr[0]['tStartLong'];
    $startDate = $trip_start_data_arr[0]['tStartDate'];
    $tDriverArrivedDate = $trip_start_data_arr[0]['tDriverArrivedDate'];
    $waiting_time_diff = strtotime($startDate) - strtotime($tDriverArrivedDate);
    $waitingTime = floor($waiting_time_diff / 60);
    $vehicleTypeID = $trip_start_data_arr[0]['iVehicleTypeId'];
    $eFareType = $trip_start_data_arr[0]['eFareType'];
    $eType = $trip_start_data_arr[0]['eType'];
    $eFlatTrip = $trip_start_data_arr[0]['eFlatTrip'];
    $fFlatTripPrice = $trip_start_data_arr[0]['fFlatTripPrice'];
    $eHailTrip = $trip_start_data_arr[0]['eHailTrip'];

    //$endDateOfTrip=@date("Y-m-d H:i:s");
    $endDateOfTrip = $trip_start_data_arr[0]['tEndDate'];
    if ($endDateOfTrip == "0000-00-00 00:00:00") {
        $endDateOfTrip = @date("Y-m-d H:i:s");
    }

    if ($eFareType == 'Hourly') {
        $sql22 = "SELECT * FROM `trip_times` WHERE iTripId='$tripId'";
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
    $fiwrite = $tripDistance;
    if ($totalTimeInMinutes_trip <= 1) {
        $FinalDistance = $tripDistance;
        $FGDTime = 0;
        $FGDDistance = 0;
    } else {
        //$FinalDistance=checkDistanceWithGoogleDirections($tripDistance,$sourcePointLatitude,$sourcePointLongitude,$destination_lat,$destination_lon);
        $FinalDistanceArr = checkDistanceWithGoogleDirections($tripDistance, $sourcePointLatitude, $sourcePointLongitude, $destination_lat, $destination_lon, "0", "", true);
        $myfile = file_put_contents('DirectionsAPI.txt', PHP_EOL . 'TRIP#' . $tripId . PHP_EOL . 'DISTANCE:' . $FinalDistanceArr['Distance'] . '  DURATION:' . $FinalDistanceArr['Time'], FILE_APPEND | LOCK_EX);
        $FinalDistance = $FinalDistanceArr['Distance'];
        $FGDTime = $FinalDistanceArr['Time'];
        $FGDDistance = $FinalDistanceArr['GDistance'];

    }
    $Matrixdist = CheckDistanceWithDistanceMatrix($sourcePointLatitude, $destination_lat, $sourcePointLongitude, $destination_lon);
    $tripDistance = $FinalDistance;
    if ($Matrixdist > $tripDistance) {
        $tripDistance = $Matrixdist;
    }
    $txt = $tripId . "|" . $FinalDistance . "|" . $fiwrite . "|" . $Matrixdist;
    $myfile = file_put_contents('example1.txt', $txt . PHP_EOL, FILE_APPEND | LOCK_EX);

    $Fare_data = calculateFare($totalTimeInMinutes_trip, $tripDistance, $vehicleTypeID, $userId, 1, $startDate, $endDateOfTrip, $couponCode, $tripId, $fMaterialFee, $fMiscFee, $fDriverDiscount, $waitingTime);
    $where = " iTripId = '" . $tripId . "'";
    //    $extrafareamount=0;
    //    if($tripDistance>=15){
    //        $extraamount=floor($tripDistance/15)/10;
    //        $extraamount=$Fare_data['total_fare']*$extraamount;
    //        $extrafareamount=$Fare_data['total_fare']+$extraamount;

    //    }
    //    else{
    //    $extrafareamount=$Fare_data['total_fare'];
    //    }

    $triptolls = calcluateTripToll($tripId);
    //    $extrafareamount=$Fare_data['total_fare']+$triptolls;
    $Data_update_trips['tEndDate'] = $endDateOfTrip;
    $Data_update_trips['tEndLat'] = $destination_lat;
    $Data_update_trips['tEndLong'] = $destination_lon;
    $Data_update_trips['tDaddress'] = $dAddress;
    $Data_update_trips['iFare'] = ceil(($Fare_data['total_fare'] + $triptolls) / 10) * 10;
    $Data_update_trips['iActive'] = $Active;
    $Data_update_trips['fTollPrice'] = $triptolls;
    $Data_update_trips['fDistance'] = $tripDistance;
    $Data_update_trips['fDuration'] = $totalTimeInMinutes_trip;
    $Data_update_trips['fPricePerMin'] = $Fare_data['fPricePerMin'];
    $Data_update_trips['fPricePerKM'] = $Fare_data['fPricePerKM'];
    $Data_update_trips['iBaseFare'] = $Fare_data['iBaseFare'];
    $Data_update_trips['fCommision'] = $Fare_data['fCommision'];
    $Data_update_trips['fDiscount'] = $Fare_data['fDiscount'];
    $Data_update_trips['vDiscount'] = $Fare_data['vDiscount'];
    $Data_update_trips['fMinFareDiff'] = $Fare_data['MinFareDiff'];
    $Data_update_trips['fSurgePriceDiff'] = $Fare_data['fSurgePriceDiff'];
    $Data_update_trips['fWalletDebit'] = $Fare_data['user_wallet_debit_amount'];
    $Data_update_trips['fTripGenerateFare'] = $Fare_data['fTripGenerateFare'];
    $Data_update_trips['fMaterialFee'] = $fMaterialFee;
    $Data_update_trips['fMiscFee'] = $fMiscFee;
    $Data_update_trips['fDriverDiscount'] = $fDriverDiscount;
    $Data_update_trips['fTax1'] = $Fare_data['fTax1'];
    $Data_update_trips['fTax2'] = $Fare_data['fTax2'];
    $Data_update_trips['fTax1Percentage'] = $Fare_data['fTax1Percentage'];
    $Data_update_trips['fTax2Percentage'] = $Fare_data['fTax2Percentage'];
    $Data_update_trips['fGDtime'] = $FGDTime;
    $Data_update_trips['fGDdistance'] = $FGDDistance;
    if ($eHailTrip == "No") {
        $Data_update_trips['fWaitingFees'] = $Fare_data['fWaitingFees'];
    } else {
        $Data_update_trips['fWaitingFees'] = 0;
    }
    $Data_update_trips['fOutStandingAmount'] = $Fare_data['fOutStandingAmount'];

    if ($isTripCanceled == "true") {
        $Data_update_trips['vCancelReason'] = $driverReason;
        $Data_update_trips['vCancelComment'] = $driverComment;
        $Data_update_trips['eCancelled'] = "Yes";
        $Data_update_trips['eCancelledBy'] = "Driver";
    }

    /*Code for Upload AfterImage of trip Start */
    if ($image_name != "") {
        //$Photo_Gallery_folder = $tconfig['tsite_upload_trip_images_path']."/".$TripID."/";
        $Photo_Gallery_folder = $tconfig['tsite_upload_trip_images_path'];
        if (!is_dir($Photo_Gallery_folder)) {
            mkdir($Photo_Gallery_folder, 0777);
        }

        $vFile = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = "pdf,doc,docx,jpg,jpeg,gif,png");
        $vImageName = $vFile[0];
        $Data_update_trips['vAfterImage'] = $vImageName;
    }
    /*Code for Upload AfterImage of trip End */
    $id = $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where);

    $trip_status = "Not Active";

    $where = " iUserId = '$userId'";
    $Data_update_passenger['iTripId'] = $tripId;
    $Data_update_passenger['vTripStatus'] = $trip_status;
    $Data_update_passenger['vCallFromDriver'] = 'Not Assigned';

    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);

    $where = " iDriverId = '$driverId'";
    $Data_update_driver['iTripId'] = $tripId;
    $Data_update_driver['vTripStatus'] = $trip_status;

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
    ## Update User Outstanding Amount ##
    $updateQuery = "UPDATE register_user set fTripsOutStandingAmount = '0' WHERE iUserId = " . $userId;
    $obj->sql_query($updateQuery);
    //$updateQury = "UPDATE trip_outstanding_amount set ePaidByPassenger = 'Yes' WHERE iUserId = ".$iUserId;
    $updateQury = "UPDATE trip_outstanding_amount set ePaidByPassenger = 'Yes',vTripAdjusmentId = '" . $tripId . "' WHERE iUserId = '" . $userId . "' AND ePaidByPassenger = 'No'";
    $obj->sql_query($updateQury);
    ## Update User Outstanding Amount ##

    if ($id > 0) {

        /*$ENABLE_PUBNUB = $generalobj->getConfigurations("configurations","ENABLE_PUBNUB");
        $PUBNUB_DISABLED = $generalobj->getConfigurations("configurations","PUBNUB_DISABLED");
        $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations","PUBNUB_PUBLISH_KEY");
        $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations","PUBNUB_SUBSCRIBE_KEY");*/
        if ($PUBNUB_DISABLED == "Yes") {
            $ENABLE_PUBNUB = "No";
        }

        /* For PubNub Setting */
        $tableName = "register_user";
        $iMemberId_VALUE = $userId;
        $iMemberId_KEY = "iUserId";
        /*$iAppVersion=get_value($tableName, 'iAppVersion', $iMemberId_KEY,$iMemberId_VALUE,'','true');
        $eDeviceType=get_value($tableName, 'eDeviceType', $iMemberId_KEY,$iMemberId_VALUE,'','true');
        $eLogout=get_value($tableName, 'eLogout', $iMemberId_KEY,$iMemberId_VALUE,'','true');
        $tLocationUpdateDate=get_value($tableName, 'tLocationUpdateDate', $iMemberId_KEY,$iMemberId_VALUE,'','true');
        $iGcmRegId=get_value($tableName, 'iGcmRegId', $iMemberId_KEY,$iMemberId_VALUE,'','true'); */
        $AppData = get_value($tableName, 'iAppVersion,eDeviceType,eLogout,tLocationUpdateDate,iGcmRegId', $iMemberId_KEY, $iMemberId_VALUE);
        $iAppVersion = $AppData[0]['iAppVersion'];
        $eDeviceType = $AppData[0]['eDeviceType'];
        $eLogout = $AppData[0]['eLogout'];
        $tLocationUpdateDate = $AppData[0]['tLocationUpdateDate'];
        $iGcmRegId = $AppData[0]['iGcmRegId'];
        /* For PubNub Setting Finished */

        $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + 60) / 60);
        $compare_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));

        //$alertSendAllowed = false;
        $alertSendAllowed = true;

        if ($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != "" /* && $iAppVersion > 1 && $eDeviceType == "Android" */) {

            //$pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);
            $pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY, "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));

            $channelName = "PASSENGER_" . $userId;

            $tSessionId = get_value("register_user", 'tSessionId', "iUserId", $userId, '', 'true');
            $message_arr['tSessionId'] = $tSessionId;
            $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

            $info = $pubnub->publish($channelName, $message_pub);

            //$message = $alertMsg;
            $tLocUpdateDate = date("Y-m-d H:i:s", strtotime($tLocationUpdateDate));
            if ($tLocUpdateDate < $compare_date) {
                $alertSendAllowed = true;
            }
            //$alertSendAllowed = true;
        } else {
            $alertSendAllowed = true;
        }

        if ($eLogout == "Yes") {
            $alertSendAllowed = false;
        }
        $deviceTokens_arr = array();

        if ($alertSendAllowed == true) {
            array_push($deviceTokens_arr, $iGcmRegId);

            if ($eDeviceType == "Android") {
                $Rmessage = array("message" => $message);

                send_notification($deviceTokens_arr, $Rmessage, 0);
            } else {
                sendApplePushNotification(0, $deviceTokens_arr, $message, $alertMsg, 0);
            }

        }

        $returnArr['Action'] = "1";
        $returnArr['iTripsLocationsID'] = $id;
        // $returnArr['TotalFare']=round($Fare_data[0]['total_fare'] * $trip_start_data_arr[0]['fRatioDriver']);
        $returnArr['TotalFare'] = round($extrafareamount * $trip_start_data_arr[0]['fRatioDriver'], 1);
        // $returnArr['CurrencySymbol']=($obj->MySQLSelect("SELECT vSymbol FROM currency WHERE vName='".$trip_start_data_arr[0]['vCurrencyDriver']."' ")[0]['vSymbol']);
        $returnArr['CurrencySymbol'] = $currencySymbolDriver;
        $returnArr['tripStartTime'] = $startDate;
        $returnArr['TripPaymentMode'] = $trip_start_data_arr[0]['vTripPaymentMode'];
        $returnArr['Discount'] = round($Fare_data['fDiscount'] * $trip_start_data_arr[0]['fRatioDriver'], 1);
        $returnArr['Message'] = "Data Updated";
        $returnArr['FormattedTripDate'] = date('dS M Y \a\t h:i a', strtotime($startDate));

        $generalobj->get_benefit_amount($tripId);

        // Code for Check last logout date is update in driver_log_report
        $query = "SELECT * FROM driver_log_report WHERE iDriverId = '" . $driverId . "' ORDER BY iDriverLogId DESC LIMIT 0,1";
        $db_driver = $obj->MySQLSelect($query);
        if (count($db_driver) > 0) {
            $driver_lastonline = @date("Y-m-d H:i:s");
            $updateQuery = "UPDATE driver_log_report set dLogoutDateTime='" . $driver_lastonline . "' WHERE iDriverLogId = " . $db_driver[0]['iDriverLogId'];
            $obj->sql_query($updateQuery);
        }
        // Code for Check last logout date is update in driver_log_report Ends

    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    //getTripChatDetails($tripId);
    echo json_encode($returnArr);

?>