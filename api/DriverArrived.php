<?php 

    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';

    if ($iDriverId != '') {

        $vTripStatus = get_value('register_driver', 'vTripStatus', 'iDriverId', $iDriverId, '', 'true');
        if ($vTripStatus == "Cancelled") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "DO_RESTART";
            echo json_encode($returnArr);exit;
        }

        $where = " iDriverId = '$iDriverId'";

        $Data_update_driver['vTripStatus'] = 'Arrived';

        $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

        if ($id > 0) {

            $sql = "SELECT CONCAT(rd.vName,' ',rd.vLastName) AS driverName, tr.vRideNo, tr.tEndLat,tr.tEndLong,tr.tDaddress,tr.iUserId,tr.eType,rd.iTripId,tr.eTollSkipped,tr.eBeforeUpload,tr.eAfterUpload FROM trips as tr,register_driver as rd WHERE tr.iTripId=rd.iTripId AND rd.iDriverId = '" . $iDriverId . "'";
            $result = $obj->MySQLSelect($sql);

            // echo "<pre>"; print_r($result);  die;

            $returnArr['Action'] = "1";

            if ($result[0]['iTripId'] != "") {
                // Update Trip Table
                $where1 = " iTripId = '" . $result[0]['iTripId'] . "'";
                $Data_update_trips['tDriverArrivedDate'] = date('Y-m-d H:i:s');
                $id = $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where1);
            }

            if ($result[0]['tEndLat'] != '' && $result[0]['tEndLong'] != '') {
                $data['DLatitude'] = $result[0]['tEndLat'];
                $data['DLongitude'] = $result[0]['tEndLong'];
                $data['DAddress'] = $result[0]['tDaddress'];
            } else {
                $data['DLatitude'] = "0";
                $data['DLongitude'] = "0";
                $data['DAddress'] = "0";
            }
            $data['eTollSkipped'] = $result[0]['eTollSkipped'];
            $data['eBeforeUpload'] = $result[0]['eBeforeUpload'];
            $data['eAfterUpload'] = $result[0]['eAfterUpload'];
            $returnArr['message'] = $data;
            // echo "UpdateSuccess";

            /*$ENABLE_PUBNUB = $generalobj->getConfigurations("configurations","ENABLE_PUBNUB");
            $PUBNUB_DISABLED = $generalobj->getConfigurations("configurations","PUBNUB_DISABLED");
            $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations","PUBNUB_PUBLISH_KEY");
            $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations","PUBNUB_SUBSCRIBE_KEY");*/
            if ($PUBNUB_DISABLED == "Yes") {
                $ENABLE_PUBNUB = "No";
            }

            /* For PubNub Setting */
            $tableName = "register_user";
            $iMemberId_VALUE = $result[0]['iUserId'];
            $iMemberId_KEY = "iUserId";
            /*$iAppVersion=get_value($tableName, 'iAppVersion', $iMemberId_KEY,$iMemberId_VALUE,'','true');
            $eDeviceType=get_value($tableName, 'eDeviceType', $iMemberId_KEY,$iMemberId_VALUE,'','true');
            $iGcmRegId=get_value($tableName, 'iGcmRegId', $iMemberId_KEY,$iMemberId_VALUE,'','true');
            $vLangCode=get_value($tableName, 'vLang', $iMemberId_KEY,$iMemberId_VALUE,'','true');*/
            $AppData = get_value($tableName, 'iAppVersion,eDeviceType,iGcmRegId,vLang', $iMemberId_KEY, $iMemberId_VALUE);
            $iAppVersion = $AppData[0]['iAppVersion'];
            $eDeviceType = $AppData[0]['eDeviceType'];
            $iGcmRegId = $AppData[0]['iGcmRegId'];
            $vLangCode = $AppData[0]['vLang'];
            /* For PubNub Setting Finished */

            if ($vLangCode == "" || $vLangCode == null) {
                $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            }

            $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
            $driverArrivedLblValue = $languageLabelsArr['LBL_DRIVER_ARRIVED_NOTIMSG'];
            $driverArrivedLblValue_delivery = $languageLabelsArr['LBL_CARRIER_ARRIVED_NOTIMSG'];
            $driverArrivedLblValue_ride = $languageLabelsArr['LBL_DRIVER_ARRIVED_TXT'];

            $deviceTokens_arr_ios = array();
            $registation_ids_new = array();
            $message = "";

            $message_arr['Message'] = "DriverArrived";
            $message_arr['MsgType'] = "DriverArrived";
            $message_arr['iDriverId'] = $iDriverId;
            $message_arr['driverName'] = $result[0]['driverName'];
            $message_arr['vRideNo'] = $result[0]['vRideNo'];
            $message_arr['iTripId'] = $result[0]['iTripId'];
            $message_arr['eType'] = $result[0]['eType'];
            $eType = $result[0]['eType'];
            if ($eType == "UberX") {
                $alertMsg = $languageLabelsArr['LBL_PROVIDER'] . ' ' . $result[0]['driverName'] . ' ' . $driverArrivedLblValue . $result[0]['vRideNo'];
            } elseif ($eType == "Deliver") {
                $alertMsg = $languageLabelsArr['LBL_CARRIER'] . ' ' . $result[0]['driverName'] . ' ' . $driverArrivedLblValue_delivery;
            } else {
                $alertMsg = $driverArrivedLblValue_ride;
            }
            $message_arr['vTitle'] = $alertMsg;
            $message = json_encode($message_arr);

            $alertSendAllowed = true;

            if ($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != "" /*  && $iAppVersion > 1 && $eDeviceType == "Android" */) {
                //$pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);
                $pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY, "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));
                $channelName = "PASSENGER_" . $result[0]['iUserId'];

                $tSessionId = get_value("register_user", 'tSessionId', "iUserId", $result[0]['iUserId'], '', 'true');
                $message_arr['tSessionId'] = $tSessionId;
                $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);
                $info = $pubnub->publish($channelName, $message_pub);
            }

            #####################Add Status Message#########################
            $DataTripMessages['tMessage'] = $message;
            $DataTripMessages['iDriverId'] = $iDriverId;
            $DataTripMessages['iTripId'] = $result[0]['iTripId'];
            $DataTripMessages['iUserId'] = $result[0]['iUserId'];
            $DataTripMessages['eFromUserType'] = "Driver";
            $DataTripMessages['eToUserType'] = "Passenger";
            $DataTripMessages['eReceived'] = "No";
            $DataTripMessages['dAddedDate'] = @date("Y-m-d H:i:s");

            $obj->MySQLQueryPerform("trip_status_messages", $DataTripMessages, 'insert');
            ################################################################

            if ($alertSendAllowed == true) {
                if ($eDeviceType == "Android") {

                    array_push($registation_ids_new, $iGcmRegId);
                    $Rmessage = array("message" => $message);
                    $result = send_notification($registation_ids_new, $Rmessage, 0);
                } else if ($eDeviceType != "Android") {
                    /*if($ENABLE_PUBNUB == "Yes"){
                    $message = "";
                    } */

                    array_push($deviceTokens_arr_ios, $iGcmRegId);
                    if ($message != "") {
                        sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
                    }
                }
            }

        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            // echo "UpdateFailed";
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    echo json_encode($returnArr);

?>