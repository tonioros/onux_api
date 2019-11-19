<?php 

    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $TripID = isset($_REQUEST["TripID"]) ? $_REQUEST["TripID"] : '';
    $image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
    $i = 0;
    if ($image_object) {
        ExifCleaning::adjustImageOrientation($image_object);
    }
    $startDateOfTrip = @date("Y-m-d H:i:s");
    $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    if ($vLangCode == "" || $vLangCode == null) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
    //    if($iUserId==1349 || $iUserId==32016){
    $estimatefare = get_value('estimatefare', 'Estimate_Fare', 'User_Id', $iUserId, '', 'true');
    $paymentmethodnow = get_value('trips', 'vTripPaymentMode', 'iTripId', $TripID, '', 'true');
    $vStripeCusId = get_value('register_user', 'vStripeCusId', 'iUserId', $iUserId, '', 'true');
    $vRideNo = get_value('trips', 'vRideNo', 'iTripId', $TripID, '', 'true');
    $currency = get_value('trips', 'vCurrencyPassenger', 'iTripId', $TripID, '', 'true');
    $description = $languageLabelsArr['LBL_TRIP_PAYMENT_RECEIVED'] . " " . $vRideNo;
    $vBrainTreeToken = get_value('register_user', 'vBrainTreeToken', 'iUserId', $iUserId, '', 'true');
    $statusofchargeid = '';

    if ($paymentmethodnow == "Card") {

        if ($APP_PAYMENT_METHOD == "Stripe") {

            if ($vStripeCusId != "") {
                $Charge_Array = array("iFare" => $estimatefare, "price_new" => $estimatefare * 100, "currency" => $currency, "vStripeCusId" => $vStripeCusId, "description" => $description, "iTripId" => $TripID, "eCancelChargeFailed" => "No", "vBrainTreeToken" => $vBrainTreeToken, "vRideNo" => $vRideNo, "iMemberId" => $iUserId, "UserType" => "Passenger");
                $ChargeidArr = ChargeCustomer($Charge_Array, "CollectPayment");
                $statusofchargeid = $ChargeidArr['status'];
                $where = " iTripId = '$TripID'";
                $eDeviceType1 = get_value('register_driver', 'eDeviceType', 'iDriverId', $iDriverId, '', 'true');
                if ($eDeviceType1 == "Ios") {
                    $Data_update_epayment['ePaymentCollect'] = 'No';
                } else {
                    $Data_update_epayment['ePaymentCollect'] = 'Yes';
                }
                $id1 = $obj->MySQLQueryPerform("trips", $Data_update_epayment, 'update', $where);

                /*    try{

            $charge_create = Stripe_Charge::create(array(
            "amount" => $estimatefare,
            "currency" => $currency,
            "customer" => $vStripeCusId,
            "description" =>  $description
            ));

            $details = json_decode($charge_create);
            $result = get_object_vars($details);

            if($result['status']=="succeeded" || $result['status']=="paid"){

            //    $ch = Stripe_Charge::retrieve($result['id']);
            //$ch->refund();
            //    $returnArr["Action"] = "1";
            //    echo json_encode($returnArr);exit;

            }else{
            $returnArr['Action'] = "0";
            $returnArr['message']="LBL_TRANS_FAILED";

            echo json_encode($returnArr);exit;
            }
            }catch (Exception $e) {
            $error3 = $e->getMessage();
            $returnArr['Action'] = "0";
            //    if($error3=="Your card has insufficient funds.")
            //    $error3="Su tarjeta tiene fondos insuficientes.";
            //    if($error3=="Your card was declined.")
            //    $error3="Tu tarjeta fue rechazada.";
            //    var_dump($result1);
            $returnArr['message']=$error3;
            //$returnArr['message']="LBL_TRY_AGAIN_LATER_TXT";

            }*/

            } else if ($APP_PAYMENT_METHOD == "Braintree") {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            }
        } else {
            $returnArr['Action'] = "1";
        }
    }
    //    }
    //    if($statusofchargeid=="success" || $statusofchargeid=="ok" ){

    $tripstartlabel = $languageLabelsArr['LBL_DRIVER_START_NOTIMSG'];
    $tripstartlabel_ride = $languageLabelsArr['LBL_START_TRIP_DIALOG_TXT'];
    $tripstartlabel_delivery = $languageLabelsArr['LBL_START_DELIVERY_DIALOG_TXT'];
    $message = "TripStarted";

    $sql = "SELECT CONCAT(rd.vName,' ',rd.vLastName) AS driverName, tr.vRideNo FROM trips as tr,register_driver as rd WHERE tr.iTripId=rd.iTripId AND rd.iDriverId = '" . $iDriverId . "'";
    $result22 = $obj->MySQLSelect($sql);

    //$verificationCode = rand ( 10000000 , 99999999 );
    $verificationCode = 1234;

    /*$eType =get_value('trips', 'eType', 'iTripId',$TripID,'','true');
    $fVisitFee = get_value('trips', 'fVisitFee', 'iTripId', $TripID,'','true');
    $eFareType = get_value('trips', 'eFareType', 'iTripId', $TripID,'','true');*/
    $TripData = get_value('trips', 'eType,fVisitFee,eFareType', 'iTripId', $TripID);
    $eType = $TripData[0]['eType'];
    $fVisitFee = $TripData[0]['fVisitFee'];
    $eFareType = $TripData[0]['eFareType'];

    if ($eType == "UberX") {
        $alertMsg = $languageLabelsArr['LBL_PROVIDER'] . ' ' . $result22[0]['driverName'] . ' ' . $tripstartlabel . $result22[0]['vRideNo'];
    } elseif ($eType == "Ride") {
        $alertMsg = $tripstartlabel_ride;
    } else {
        $alertMsg = $tripstartlabel_delivery;
    }
    $message_arr = array();
    $message_arr['Message'] = $message;
    $message_arr['iDriverId'] = $iDriverId;
    $message_arr['iTripId'] = $TripID;
    $message_arr['driverName'] = $result22[0]['driverName'];
    $message_arr['vRideNo'] = $result22[0]['vRideNo'];
    if ($eType == "Deliver") {
        $message_arr['VerificationCode'] = strval($verificationCode);
    } else {
        $message_arr['VerificationCode'] = "";
    }
    $message_arr['vTitle'] = $alertMsg;
    $message_arr['eType'] = $eType;

    $message = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

    #####################Add Status Message#########################
    $DataTripMessages['tMessage'] = $message;
    $DataTripMessages['iDriverId'] = $iDriverId;
    $DataTripMessages['iTripId'] = $TripID;
    $DataTripMessages['iUserId'] = $iUserId;
    $DataTripMessages['eFromUserType'] = "Driver";
    $DataTripMessages['eToUserType'] = "Passenger";
    $DataTripMessages['eReceived'] = "No";
    $DataTripMessages['dAddedDate'] = @date("Y-m-d H:i:s");

    $obj->MySQLQueryPerform("trip_status_messages", $DataTripMessages, 'insert');
    ################################################################

    //Update passenger Table
    $where = " iUserId = '$iUserId'";

    $Data_update_passenger['vTripStatus'] = 'On Going Trip';

    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);

    //Update Driver Table
    $where = " iDriverId = '$iDriverId'";

    $Data_update_driver['vTripStatus'] = 'On Going Trip';

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

    $sql = "SELECT iGcmRegId,eDeviceType,iTripId,tLocationUpdateDate,eLogout,tSessionId FROM register_user WHERE iUserId='$iUserId'";
    $result = $obj->MySQLSelect($sql);

    // $Curr_TripID=$result[0]['iTripId'];

    $where = " iTripId = '$TripID'";

    $Data_update_trips['iActive'] = 'On Going Trip';
    $Data_update_trips['tStartDate'] = $startDateOfTrip;

    /*Code for Upload StartImage of trip Start */
    if ($image_name != "") {
        //$Photo_Gallery_folder = $tconfig['tsite_upload_trip_images_path']."/".$TripID."/";
        $Photo_Gallery_folder = $tconfig['tsite_upload_trip_images_path'];
        if (!is_dir($Photo_Gallery_folder)) {
            mkdir($Photo_Gallery_folder, 0777);
        }

        $vFile = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = "pdf,doc,docx,jpg,jpeg,gif,png");
        $vImageName = $vFile[0];
        $Data_update_trips['vBeforeImage'] = $vImageName;
    }
    /*Code for Upload StartImage of trip End */
    $id = $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where);

    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['fVisitFee'] = $fVisitFee;

        /*$ENABLE_PUBNUB = $generalobj->getConfigurations("configurations","ENABLE_PUBNUB");
        $PUBNUB_DISABLED = $generalobj->getConfigurations("configurations","PUBNUB_DISABLED");
        $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations","PUBNUB_PUBLISH_KEY");
        $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations","PUBNUB_SUBSCRIBE_KEY");*/
        if ($PUBNUB_DISABLED == "Yes") {
            $ENABLE_PUBNUB = "No";
        }

        /* For PubNub Setting */
        $tableName = "register_user";
        $iMemberId_VALUE = $iUserId;
        $iMemberId_KEY = "iUserId";
        /*$iAppVersion=get_value($tableName, 'iAppVersion', $iMemberId_KEY,$iMemberId_VALUE,'','true');
        $eDeviceType=get_value($tableName, 'eDeviceType', $iMemberId_KEY,$iMemberId_VALUE,'','true');*/
        $AppData = get_value($tableName, 'iAppVersion,eDeviceType', $iMemberId_KEY, $iMemberId_VALUE);
        $iAppVersion = $AppData[0]['iAppVersion'];
        $eDeviceType = $AppData[0]['eDeviceType'];
        /* For PubNub Setting Finished */

        $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + 60) / 60);
        $compare_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));

        //$alertSendAllowed = false;
        $alertSendAllowed = true;

        if ($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != "" /*  && $iAppVersion > 1 && $eDeviceType == "Android" */) {

            //$pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);
            $pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY, "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));

            $channelName = "PASSENGER_" . $iUserId;

            //$tSessionId=get_value("register_user", 'tSessionId', "iUserId",$iUserId,'','true');
            $tSessionId = $result[0]['tSessionId'];
            $message_arr['tSessionId'] = $tSessionId;
            $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);
            $info = $pubnub->publish($channelName, $message_pub);

            //$message = $alertMsg;
            $tLocUpdateDate = date("Y-m-d H:i:s", strtotime($result[0]['tLocationUpdateDate']));
            if ($tLocUpdateDate < $compare_date) {
                $alertSendAllowed = true;
            }
            //$alertSendAllowed = true;
        } else {
            $alertSendAllowed = true;
        }
        if ($result[0]['eLogout'] == "Yes") {
            $alertSendAllowed = false;
        }

        $deviceTokens_arr = array();

        if ($alertSendAllowed == true) {
            array_push($deviceTokens_arr, $result[0]['iGcmRegId']);

            if ($result[0]['eDeviceType'] == "Android") {
                $Rmessage = array("message" => $message);

                send_notification($deviceTokens_arr, $Rmessage, 0);
            } else {
                sendApplePushNotification(0, $deviceTokens_arr, $message, $alertMsg, 0);
            }

        }

        // Send SMS to receiver if trip type is delivery.
        if ($eType == "Deliver") {
            $receiverMobile = get_value('trips', 'vReceiverMobile', 'iTripId', $TripID, '', 'true');
            $receiverMobile1 = "+" . $receiverMobile;

            $where_trip_update = " iTripId = '$TripID'";
            $data_delivery['vDeliveryConfirmCode'] = $verificationCode;
            $obj->MySQLQueryPerform("trips", $data_delivery, 'update', $where);

            //$message_deliver = "SMS format goes here. Your verification code is ".$verificationCode." Please give this code to driver to end delivery process.";
            $message_deliver = deliverySmsToReceiver($TripID);
            $result = sendEmeSms($receiverMobile1, $message_deliver);
            if ($result == 0) {
                //$isdCode= $generalobj->getConfigurations("configurations","SITE_ISD_CODE");
                $isdCode = $SITE_ISD_CODE;
                $receiverMobile = "+" . $isdCode . $receiverMobile;
                sendEmeSms($receiverMobile, $message_deliver);
            }

            $returnArr['message'] = $verificationCode;
            $returnArr['SITE_TYPE'] = SITE_TYPE;
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    $returnArr['iTripTimeId'] = '';
    if ($eFareType == 'Hourly') {
        $dTime = date('Y-m-d H:i:s');
        $Data_update['dResumeTime'] = $dTime;
        $Data_update['iTripId'] = $TripID;
        $id = $obj->MySQLQueryPerform("trip_times", $Data_update, 'insert');
        $returnArr['iTripTimeId'] = $id;
    }
    //    if( isset(    $_SESSION['estimate_fare']) ){
    //    $sessionid=get_value('register_user', 'tSessionId', 'iUserId',$iUserId,'','true');
    //    $returnArr['Action'] = "0";
    //        $returnArr['message']="hello12".$_SESSION['estimate_fare']."hh";
    //    }
    //    }
    //    $returnArr['message']=$statusofchargeid;
    echo json_encode($returnArr);


?>