<?php 

    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $driverComment = isset($_REQUEST["Comment"]) ? $_REQUEST["Comment"] : '';
    $driverReason = isset($_REQUEST["Reason"]) ? $_REQUEST["Reason"] : '';
    $eConfirmByUser = isset($_REQUEST['eConfirmByUser']) ? $_REQUEST['eConfirmByUser'] : 'No';
    if ($eConfirmByUser == "" || $eConfirmByUser == null) {
        $eConfirmByUser = "No";
    }
    $eWalletAdjustment = get_value('register_user', 'eWalletAdjustment', 'iUserId', $iUserId, '', 'true');

    if ($userType != "Driver") {
        //$vTripStatus = get_value('register_user', 'vTripStatus', 'iUserId',$iUserId,'','true');
        $vTripStatus = get_value('trips', 'iActive', 'iTripId', $iTripId, '', 'true');

        if ($vTripStatus == "Canceled" || $vTripStatus == "Cancelled" || $vTripStatus == "Finished" || $vTripStatus == "On Going Trip") {

            $returnArr['Action'] = "0";
            $returnArr['message'] = "DO_RESTART";
            echo json_encode($returnArr);
            exit;
        }
    }
    $tripCancelData = get_value('trips AS tr LEFT JOIN vehicle_type AS vt ON vt.iVehicleTypeId=tr.iVehicleTypeId', 'tr.vCouponCode,tr.vTripPaymentMode,tr.iUserId,tr.iFare,tr.vRideNo,tr.tStartDate,tr.tTripRequestDate,tr.tDriverArrivedDate,tr.eType,vt.fCancellationFare,vt.iCancellationTimeLimit,vt.iWaitingFeeTimeLimit', 'iTripId', $iTripId);

    if ($iUserId == "" || $iUserId == null || $iUserId == 0) {
        $iUserId = $tripCancelData[0]['iUserId'];
    }
    $tStartDate = $tripCancelData[0]['tStartDate'];
    $tTripRequestDate = $tripCancelData[0]['tTripRequestDate'];
    $tDriverArrivedDate = $tripCancelData[0]['tDriverArrivedDate'];
    if ($userType != "Driver") {
        $currentDate = @date("Y-m-d H:i:s");
    } else {
        $currentDate = @date("Y-m-d H:i:s");
        $tTripRequestDate = $tDriverArrivedDate;
        if ($tTripRequestDate == "0000-00-00 00:00:00") {
            $tTripRequestDate = @date("Y-m-d H:i:s");
        }
    }

    $fCancellationFare = 0;
    if ($tDriverArrivedDate == "0000-00-00 00:00:00") {
        $fWaitingFees = 0;
    } else {
        $fWaitingFees = getTripWaitingFee($iTripId);
    }
    $fWaitingFees = 0; // As per discussion now waiting fee is not charge when cancel trip
    $eCancelChargeFailed = "No";
    $totalMinute = @round(abs(strtotime($currentDate) - strtotime($tTripRequestDate)) / 60, 2);
    //if($totalMinute >= $tripCancelData[0]['iCancellationTimeLimit'] && $userType != "Driver"){

    if ($totalMinute >= $tripCancelData[0]['iCancellationTimeLimit']) {
        ## Display Trip cancellation charge message to user ##
        if ($eConfirmByUser == "No" && $userType != "Driver" && $tripCancelData[0]['fCancellationFare'] > 0) {
            $TripType = $tripCancelData[0]['eType'];
            $vLangCode = get_value("register_user", "vLang", "iUserId", $iUserId, '', 'true');
            if ($vLangCode == "" || $vLangCode == null) {
                $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            }
            if ($TripType == "Ride") {
                $cancelMsg_db = "LBL_CANCELTRIP_RIDE_CHARGE_TXT";
            } elseif ($TripType == "UberX") {
                $cancelMsg_db = "LBL_CANCELTRIP_SERVICE_CHARGE_TXT";
            } else {
                $cancelMsg_db = "LBL_CANCELTRIP_DELIVER_CHARGE_TXT";
            }
            $returnArr['Action'] = "0";
            $returnArr['message'] = $cancelMsg_db;
            $returnArr['isCancelChargePopUpShow'] = "Yes";
            echo json_encode($returnArr);
            exit;
        }
        ## Display Trip cancellation charge message to user ##
        $fCancellationFare = $tripCancelData[0]['fCancellationFare'];
        $fCancellationFare = $fCancellationFare + $fWaitingFees;
        $vTripPaymentMode = $tripCancelData[0]['vTripPaymentMode'];

        /* Check debit wallet For Cancel Charge */
        if ($fCancellationFare > 0 && $eWalletAdjustment == "Yes") {
            $user_available_balance = $generalobj->get_user_available_balance($iUserId, "Rider");
            $user_wallet_debit_amount = 0;
            if ($fCancellationFare > $user_available_balance) {
                $fCancellationFare = $fCancellationFare - $user_available_balance;
                $user_wallet_debit_amount = $user_available_balance;
            } else {
                $user_wallet_debit_amount = $fCancellationFare;
                $fCancellationFare = 0;
                $updateQuery = "UPDATE trips set fWalletDebit = '" . $user_wallet_debit_amount . "' WHERE iTripId = " . $iTripId;
                $obj->sql_query($updateQuery);
                $iTripOutstandId = UpdateTripOutstandingAmount($iTripId, "Yes", "Yes");
            }
        }
        /* Check debit wallet For Cancel Charge */

        if ($vTripPaymentMode == "Card" && $fCancellationFare > 0) {
            $vStripeCusId = get_value('register_user', 'vStripeCusId', 'iUserId', $tripCancelData[0]['iUserId'], '', 'true');
            $vBrainTreeToken = get_value('register_user', 'vBrainTreeToken', 'iUserId', $tripCancelData[0]['iUserId'], '', 'true');
            $currency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
            $price_new = $fCancellationFare * 100;
            $description = "Payment received for cancelled trip number:" . $tripCancelData[0]['vRideNo'];
            $Charge_Array = array("iFare" => $fCancellationFare, "price_new" => $price_new, "currency" => $currency, "vStripeCusId" => $vStripeCusId, "description" => $description, "iTripId" => $iTripId, "eCancelChargeFailed" => $eCancelChargeFailed, "vBrainTreeToken" => $vBrainTreeToken, "vRideNo" => $tripCancelData[0]['vRideNo'], "iMemberId" => $tripCancelData[0]['iUserId'], "UserType" => "Passenger");
            $ChargeidArr = ChargeCustomer($Charge_Array, "cancelTrip"); // function for charge customer
            $ChargeidArrId = $ChargeidArr['id'];
            $eCancelChargeFailed = $ChargeidArr['eCancelChargeFailed'];
            $status = $ChargeidArr['status'];
            /*try{
        if($fCancellationFare > 0){
        $charge_create = Stripe_Charge::create(array(
        "amount" => $price_new,
        "currency" => $currency,
        "customer" => $vStripeCusId,
        "description" =>  $description
        ));
        $details = json_decode($charge_create);
        $result = get_object_vars($details);
        if($fCancellationFare == 0 || ($result['status']=="succeeded" && $result['paid']=="1")){
        $pay_data['tPaymentUserID']=$result['id'];
        $pay_data['vPaymentUserStatus']="approved";
        $pay_data['iTripId']=$iTripId;
        $pay_data['iAmountUser']=$fCancellationFare;
        $obj->MySQLQueryPerform("payments",$pay_data,'insert');
        }else{
        $eCancelChargeFailed ='Yes';
        }
        }
        }catch(Exception $e){
        $error3 = $e->getMessage();
        $eCancelChargeFailed ='Yes';
        } */
        }
        if ($vTripPaymentMode == "Cash" && $fCancellationFare > 0) {
            $eCancelChargeFailed = 'Yes';
        }
    }
    $active_status = "Canceled";
    if ($userType != "Driver") {
        $message = "TripCancelled";
    } else {
        $message = "TripCancelledByDriver";
    }

    $couponCode = $tripCancelData[0]['vCouponCode'];

    if ($couponCode != '') {
        $noOfCouponUsed = get_value('coupon', 'iUsed', 'vCouponCode', $couponCode, '', 'true');

        $where = " vCouponCode = '" . $couponCode . "'";
        $data_coupon['iUsed'] = $noOfCouponUsed - 1;
        $obj->MySQLQueryPerform("coupon", $data_coupon, 'update', $where);
    }

    $statusUpdate_user = "Not Assigned";
    $trip_status = "Cancelled";

    $sql = "SELECT CONCAT(rd.vName,' ',rd.vLastName) AS driverName, tr.vRideNo, tr.eType FROM trips as tr,register_driver as rd WHERE tr.iTripId=rd.iTripId AND rd.iDriverId = '" . $iDriverId . "'";
    $result = $obj->MySQLSelect($sql);
    /* For PubNub Setting */
    $tableName = $userType != "Driver" ? "register_driver" : "register_user";
    $iMemberId_VALUE = $userType != "Driver" ? $iDriverId : $iUserId;
    $iMemberId_KEY = $userType != "Driver" ? "iDriverId" : "iUserId";
    /*$iAppVersion=get_value($tableName, 'iAppVersion', $iMemberId_KEY,$iMemberId_VALUE,'','true');
    $eLogout=get_value($tableName, 'eLogout', $iMemberId_KEY,$iMemberId_VALUE,'','true');
    $eDeviceType=get_value($tableName, 'eDeviceType', $iMemberId_KEY,$iMemberId_VALUE,'','true');*/
    $AppData = get_value($tableName, 'iAppVersion,eDeviceType,eLogout,vLang', $iMemberId_KEY, $iMemberId_VALUE);
    $iAppVersion = $AppData[0]['iAppVersion'];
    $eLogout = $AppData[0]['eLogout'];
    $eDeviceType = $AppData[0]['eDeviceType'];
    /* For PubNub Setting Finished */
    /*$ENABLE_PUBNUB = $generalobj->getConfigurations("configurations","ENABLE_PUBNUB");
    $PUBNUB_DISABLED = $generalobj->getConfigurations("configurations","PUBNUB_DISABLED");
    $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations","PUBNUB_PUBLISH_KEY");
    $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations","PUBNUB_SUBSCRIBE_KEY");*/
    if ($PUBNUB_DISABLED == "Yes") {
        $ENABLE_PUBNUB = "No";
    }

    $alertMsg = "Trip canceled";
    //$vLangCode=get_value($tableName, 'vLang', $iMemberId_KEY,$iMemberId_VALUE,'','true');
    $vLangCode = $AppData[0]['vLang'];
    if ($vLangCode == "" || $vLangCode == null) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $eType = $result[0]['eType'];
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
    if ($userType == "Driver") {
        if ($eType == "Ride") {
            $usercanceltriplabel = $languageLabelsArr['LBL_PREFIX_TRIP_CANCEL_DRIVER'] . ' ' . $languageLabelsArr['LBL_CANCEL_TRIP_BY_DRIVER_MSG_SUFFIX'];
        } elseif ($eType == "Deliver") {
            $usercanceltriplabel = $languageLabelsArr['LBL_PREFIX_DELIVERY_CANCEL_DRIVER'] . ' ' . $languageLabelsArr['LBL_CANCEL_DELIVERY_BY_DRIVER_MSG_SUFFIX'];
        } else {
            $usercanceltriplabel = $languageLabelsArr['LBL_PREFIX_JOB_CANCEL_PROVIDER'] . ' ' . $languageLabelsArr['LBL_CANCEL_UBERX_BOOKING_BY_DRIVER_MSG_SUFFIX'];
        }
    } else {
        if ($eType == "Ride") {
            $usercanceltriplabel = $languageLabelsArr['LBL_PASSENGER_CANCEL_TRIP_TXT'];
        } elseif ($eType == "Deliver") {
            $usercanceltriplabel = $languageLabelsArr['LBL_SENDER_CANCEL_DELIVERY_TXT'];
        } else {
            $usercanceltriplabel = $languageLabelsArr['LBL_USER_CANCEL_JOB_TXT'];
        }

    }
    $alertMsg = $usercanceltriplabel;

    $message_arr = array();
    $message_arr['Message'] = $message;
    if ($userType == "Driver") {
        $message_arr['Reason'] = $driverReason;
        $message_arr['isTripStarted'] = "false";
    }
    $message_arr['iTripId'] = $iTripId;
    $message_arr['iDriverId'] = $iDriverId;
    $message_arr['iUserId'] = $iUserId;
    $message_arr['driverName'] = $result[0]['driverName'];
    $message_arr['vRideNo'] = $result[0]['vRideNo'];
    $message_arr['eType'] = $result[0]['eType'];
    $message_arr['vTitle'] = $alertMsg;

    $message = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

    #####################Add Status Message#########################
    $DataTripMessages['tMessage'] = $message;
    $DataTripMessages['iDriverId'] = $iDriverId;
    $DataTripMessages['iTripId'] = $iTripId;
    $DataTripMessages['iUserId'] = $iUserId;
    if ($userType != "Driver") {
        $DataTripMessages['eFromUserType'] = "Passenger";
        $DataTripMessages['eToUserType'] = "Driver";
    } else {
        $DataTripMessages['eFromUserType'] = "Driver";
        $DataTripMessages['eToUserType'] = "Passenger";
    }
    $DataTripMessages['eReceived'] = "No";
    $DataTripMessages['dAddedDate'] = @date("Y-m-d H:i:s");

    $obj->MySQLQueryPerform("trip_status_messages", $DataTripMessages, 'insert');
    ################################################################

    $where = " iTripId = '$iTripId'";
    $Data_update_trips['iActive'] = $active_status;
    $Data_update_trips['tEndDate'] = @date("Y-m-d H:i:s");
    $Data_update_trips['fWaitingFees'] = $fWaitingFees;
    $Data_update_trips['fWalletDebit'] = $user_wallet_debit_amount;
    if ($tStartDate == "0000-00-00 00:00:00") {
        $Data_update_trips['tStartDate'] = @date("Y-m-d H:i:s");
    }
    if ($tDriverArrivedDate == "0000-00-00 00:00:00") {
        $Data_update_trips['tDriverArrivedDate'] = @date("Y-m-d H:i:s");
    }
    //if($vTripPaymentMode == "Card" && $fCancellationFare > 0){
    if ($fCancellationFare > 0) {
        $Data_update_trips['eCancelChargeFailed'] = $eCancelChargeFailed;
        $Data_update_trips['fCancellationFare'] = $fCancellationFare;
    }

    $Data_update_trips['eCancelledBy'] = $userType;
    if ($userType == "Driver") {
        $Data_update_trips['vCancelReason'] = $driverReason;
        $Data_update_trips['vCancelComment'] = $driverComment;
        $Data_update_trips['eCancelled'] = "Yes";
    }

    $id = $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where);

    ## Update Passenger OutStanding Amount ##
    if ($eCancelChargeFailed == "Yes" && $fCancellationFare > 0) {
        $iTripOutstandId = UpdateTripOutstandingAmount($iTripId, "No", "No");
    }
    if ($eCancelChargeFailed == "No" && $vTripPaymentMode == "Card" && $fCancellationFare > 0) {
        $iTripOutstandId = UpdateTripOutstandingAmount($iTripId, "Yes", "No");
    }
    ## Update Passenger OutStanding Amount ##

    $where = " iUserId = '$iUserId'";
    $Data_update_passenger['vCallFromDriver'] = $statusUpdate_user;
    $Data_update_passenger['vTripStatus'] = $trip_status;

    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);

    $where = " iDriverId='$iDriverId'";
    // $Data_update_driver['iTripId']=$statusUpdate_user;
    $Data_update_driver['vTripStatus'] = $trip_status;

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

    if ($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != "" /*  && $iAppVersion > 1 && $eDeviceType == "Android" */) {

        //$pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);
        $pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY, "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));

        if ($userType != "Driver") {
            $channelName = "DRIVER_" . $iDriverId;
            $tSessionId = get_value("register_driver", 'tSessionId', "iDriverId", $iDriverId, '', 'true');
        } else {
            $channelName = "PASSENGER_" . $iUserId;
            $tSessionId = get_value("register_user", 'tSessionId', "iUserId", $iUserId, '', 'true');
        }
        $message_arr['tSessionId'] = $tSessionId;
        $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

        $info = $pubnub->publish($channelName, $message_pub);

    }

    if ($userType != "Driver") {
        $sql = "SELECT iGcmRegId,eDeviceType,tLocationUpdateDate FROM register_driver WHERE iDriverId IN (" . $iDriverId . ")";
    } else {
        $sql = "SELECT iGcmRegId,eDeviceType,tLocationUpdateDate FROM register_user WHERE iUserId IN (" . $iUserId . ")";
    }

    $result = $obj->MySQLSelect($sql);

    $deviceTokens_arr_ios = array();
    $registation_ids_new = array();

    foreach ($result as $item) {
        if ($item['eDeviceType'] == "Android") {
            array_push($registation_ids_new, $item['iGcmRegId']);
        } else {
            array_push($deviceTokens_arr_ios, $item['iGcmRegId']);
        }
    }

    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + 60) / 60);
    $compare_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));

    //$alertSendAllowed = false;
    $alertSendAllowed = true;

    if ($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != "") {
        //$message = $alertMsg;
        $tLocUpdateDate = date("Y-m-d H:i:s", strtotime($result[0]['tLocationUpdateDate']));

        if ($tLocUpdateDate < $compare_date) {
            $alertSendAllowed = true;
        }
    } else {
        $alertSendAllowed = true;
    }
    if ($eLogout == "Yes") {
        $alertSendAllowed = false;
    }

    if ($alertSendAllowed == true) {
        if (count($registation_ids_new) > 0) {
            $Rmessage = array("message" => $message);

            $result = send_notification($registation_ids_new, $Rmessage, 0);
        }
        if (count($deviceTokens_arr_ios) > 0) {

            if ($userType == "Driver") {
                sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
            } else {
                sendApplePushNotification(1, $deviceTokens_arr_ios, $message, $alertMsg, 0);
            }

        }
    }

    // Code for Check last logout date is update in driver_log_report

    $driverId_log = get_value('trips', 'iDriverId', 'iTripId', $iTripId, '', 'true');
    $query = "SELECT * FROM driver_log_report WHERE iDriverId = '" . $driverId_log . "' ORDER BY iDriverLogId DESC LIMIT 0,1";
    $db_driver = $obj->MySQLSelect($query);
    if (count($db_driver) > 0) {
        $driver_lastonline = @date("Y-m-d H:i:s");
        $updateQuery = "UPDATE driver_log_report set dLogoutDateTime='" . $driver_lastonline . "' WHERE iDriverLogId = " . $db_driver[0]['iDriverLogId'];
        $obj->sql_query($updateQuery);
    }
    // Code for Check last logout date is update in driver_log_report Ends

    //getTripChatDetails($iTripId);
    $returnArr['Action'] = "1";
    $eType = $tripCancelData[0]['eType'];
    if ($eType == "Ride") {
        $label = "LBL_SUCCESS_TRIP_CANCELED";
    } elseif ($eType == "UberX") {
        $label = "LBL_SUCCESS_BOOKING_CANCELED";
    } else {
        $label = "LBL_SUCCESS_DELIVERY_CANCELED";
    }
    if ($userType == "Passenger") {
        $returnArr['message'] = getPassengerDetailInfo($iUserId, "");
        $returnArr['message1'] = $label;
    } else {
        $returnArr['message1'] = $label;
    }

    if ($userType == "Passenger") {
        sendTripReceipt($iTripId);
    } else {
        sendTripReceiptAdmin($iTripId);
    }

    echo json_encode($returnArr);

?>