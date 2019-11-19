<?php 

//$userId     = isset($_REQUEST["UserId"]) ? $_REQUEST["UserId"] : '';
$Latitude = isset($_REQUEST["Latitude"]) ? $_REQUEST["Latitude"] : '';
$Longitude = isset($_REQUEST["Longitude"]) ? $_REQUEST["Longitude"] : '';
$Address = isset($_REQUEST["Address"]) ? $_REQUEST["Address"] : '';
$userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
//$iDriverId     = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
$iTripId = isset($_REQUEST["TripId"]) ? $_REQUEST["TripId"] : '';
$eConfirmByUser = isset($_REQUEST['eConfirmByUser']) ? $_REQUEST['eConfirmByUser'] : 'No';
$eTollConfirmByUser = isset($_REQUEST['eTollConfirmByUser']) ? $_REQUEST['eTollConfirmByUser'] : 'No';
$iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
$UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
$fTollPrice = isset($_REQUEST["fTollPrice"]) ? $_REQUEST["fTollPrice"] : '';
$vTollPriceCurrencyCode = isset($_REQUEST["vTollPriceCurrencyCode"]) ? $_REQUEST["vTollPriceCurrencyCode"] : '';
$eTollSkipped = isset($_REQUEST["eTollSkipped"]) ? $_REQUEST["eTollSkipped"] : 'Yes';

if ($eConfirmByUser == "" || $eConfirmByUser == null) {
    $eConfirmByUser = "No";
}

if ($eTollConfirmByUser == "" || $eTollConfirmByUser == null) {
    $eTollConfirmByUser = "No";
}

if ($UserType == "Passenger") {
    $tblname = "register_user";
    $iUserId = "iUserId";
    $vCurrency = "vCurrencyPassenger";
    $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol,cu.Ratio FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iMemberId . "'";
    $passengerData = $obj->MySQLSelect($sqlp);
    $currencycode = $passengerData[0]['vCurrencyPassenger'];
    $currencySymbol = $passengerData[0]['vSymbol'];
    $priceRatio = $passengerData[0]['Ratio'];
    $vLangCode = $passengerData[0]['vLang'];
} else {
    $tblname = "register_driver";
    $iUserId = "iDriverId";
    $vCurrency = "vCurrencyDriver";
    $sqld = "SELECT rd.vCurrencyDriver,rd.vLang,cu.vSymbol,cu.Ratio FROM register_driver as rd LEFT JOIN currency as cu ON rd.vCurrencyDriver = cu.vName WHERE iDriverId = '" . $iMemberId . "'";
    $driverData = $obj->MySQLSelect($sqld);
    $currencycode = $driverData[0]['vCurrencyDriver'];
    $currencySymbol = $driverData[0]['vSymbol'];
    $priceRatio = $driverData[0]['Ratio'];
    $vLangCode = $driverData[0]['vLang'];
}

if ($currencycode == "" || $currencycode == null) {
    $sql = "SELECT vName,vSymbol,Ratio from currency WHERE eDefault = 'Yes'";
    $currencyData = $obj->MySQLSelect($sql);
    $currencycode = $currencyData[0]['vName'];
    $currencySymbol = $currencyData[0]['vSymbol'];
    $priceRatio = $currencyData[0]['Ratio'];
}

if ($vLangCode == "" || $vLangCode == null) {
    $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
}

$sql = "SELECT * FROM `language_label` WHERE vLabel = 'LBL_AT_TXT' AND vCode = '" . $vLangCode . "'";
$db_label = $obj->MySQLSelect($sql);
$LBL_AT_TXT = $db_label[0]['vValue'];

$dropofflocationarr = array($Latitude, $Longitude);

$ChangeAddress = "No";
// changed for rental
$sql_trip = "SELECT iUserId,iDriverId,tStartLat,tStartLong,tEndLat as TripEndLat,tEndLong as TripEndLong,fPickUpPrice,fNightPrice,iVehicleTypeId,iRentalPackageId from trips WHERE iTripId='" . $iTripId . "'";
$data_trip = $obj->MySQLSelect($sql_trip);
$userId = $data_trip[0]['iUserId'];
$iDriverId = $data_trip[0]['iDriverId'];
$TripEndLat = $data_trip[0]['TripEndLat'];
$TripEndLong = $data_trip[0]['TripEndLong'];
$tStartLat = $data_trip[0]['tStartLat'];
$tStartLong = $data_trip[0]['tStartLong'];
$fPickUpPrice = $data_trip[0]['fPickUpPrice'];
$fNightPrice = $data_trip[0]['fNightPrice'];
$iVehicleTypeId = $data_trip[0]['iVehicleTypeId'];
/*changed for rental*/
$iRentalPackageId = $data_trip[0]['iRentalPackageId'];
if ($TripEndLat != "" && $TripEndLong != "") {
    $ChangeAddress = "Yes";
}

$allowed_ans = checkAllowedAreaNew($dropofflocationarr, "Yes");
if ($allowed_ans == "No") {
    $returnArr['Action'] = "0";
    $returnArr['message'] = "LBL_DROP_LOCATION_NOT_ALLOW";
    echo json_encode($returnArr);exit;
}

if ($userType != "Driver") {
    //$sql = "SELECT ru.iTripId,tr.iDriverId,rd.vTripStatus as driverStatus,rd.iGcmRegId as regId,rd.eDeviceType as deviceType FROM register_user as ru,trips as tr,register_driver as rd WHERE ru.iUserId='$userId' AND tr.iTripId=ru.iTripId AND rd.iDriverId=tr.iDriverId";
    $sql = "SELECT rd.vTripStatus as driverStatus,rd.iGcmRegId as regId,rd.eDeviceType as deviceType,rd.vLatitude as tDriverLatitude,rd.vLongitude as tDriverLongitude FROM register_driver as rd WHERE rd.iDriverId='" . $iDriverId . "'";
} else {
    //$sql = "SELECT rd.iTripId,rd.vTripStatus as driverStatus,ru.iGcmRegId as regId,ru.eDeviceType as deviceType FROM trips as tr,register_driver as rd ,register_user as ru WHERE ru.iUserId='$userId' AND rd.iDriverId='$iDriverId'";
    $sql = "SELECT rd.vTripStatus as driverStatus,ru.iGcmRegId as regId,ru.eDeviceType as deviceType,rd.vLatitude as tDriverLatitude,rd.vLongitude as tDriverLongitude FROM register_driver as rd ,register_user as ru WHERE ru.iUserId='$userId' AND rd.iDriverId='$iDriverId'";
}

$data = $obj->MySQLSelect($sql);

if (count($data) > 0) {
    $driverStatus = $data[0]['driverStatus'];

    ######### Checking For Flattrip #########
    $sourceLocationArr = array($tStartLat, $tStartLong);
    $destinationLocationArr = array($Latitude, $Longitude);
    $data_flattrip = checkFlatTripnew($sourceLocationArr, $destinationLocationArr, $iVehicleTypeId, $iRentalPackageId);
    $eFlatTrip = $data_flattrip['eFlatTrip'];
    $fFlatTripPrice = $data_flattrip['Flatfare'];

    if ($eFlatTrip == "Yes") {
        // Changed for rental
        $data_surgePrice = checkSurgePrice($iVehicleTypeId, "", $iRentalPackageId);

        $SurgePriceValue = 1;
        $SurgePrice = "";
        if ($data_surgePrice['Action'] == "0") {
            if ($data_surgePrice['message'] == "LBL_PICK_SURGE_NOTE") {
                $fPickUpPrice = $data_surgePrice['SurgePriceValue'];
            } else {
                $fNightPrice = $data_surgePrice['SurgePriceValue'];
            }
            $SurgePriceValue = $data_surgePrice['SurgePriceValue'];
            $SurgePrice = $data_surgePrice['SurgePrice'];
        }

        if ($APPLY_SURGE_ON_FLAT_FARE == "No" && $data_flattrip['eFlatTrip'] == "Yes") {
            $fPickUpPrice = 1;
            $fNightPrice = 1;
            $SurgePriceValue = 1;
            $SurgePrice = "";
        }

        if ($eConfirmByUser == "No" && $eFlatTrip == "Yes") {
            $TripPrice = round($fFlatTripPrice * $priceRatio, 2);
            $fSurgePriceDiff = round(($TripPrice * $SurgePriceValue) - $TripPrice, 2);
            $TripPrice = $TripPrice + $fSurgePriceDiff;
            $returnArr['Action'] = "0";
            $returnArr['message'] = "Yes";
            $returnArr['eFlatTrip'] = $eFlatTrip;
            $returnArr['SurgePrice'] = ""; // $SurgePrice
            $returnArr['SurgePriceValue'] = ""; // $SurgePriceValue
            $returnArr['fFlatTripPrice'] = $TripPrice;
            if ($SurgePriceValue > 1) {
                $returnArr['fFlatTripPricewithsymbol'] = $currencySymbol . " " . $TripPrice . " (" . $LBL_AT_TXT . " " . $SurgePrice . ")";
            } else {
                $returnArr['fFlatTripPricewithsymbol'] = $currencySymbol . " " . $TripPrice;
            }
            echo json_encode($returnArr);exit;
        }

        $Data_trips['fTollPrice'] = "0";
        $Data_trips['vTollPriceCurrencyCode'] = "";
        $Data_trips['eTollSkipped'] = "No";
    } else {
        $eFlatTrip = "No";
        $fFlatTripPrice = 0;
        $Data_trips['fTollPrice'] = "0";
        $Data_trips['vTollPriceCurrencyCode'] = "";
        $Data_trips['eTollSkipped'] = "No";
    }
    ######### Checking For Flattrip #########

    $where_trip = " iTripId = '" . $iTripId . "'";
    $Data_trips['tEndLat'] = $Latitude;
    $Data_trips['tEndLong'] = $Longitude;
    $Data_trips['tDaddress'] = $Address;
    $Data_trips['eFlatTrip'] = $eFlatTrip;
    $Data_trips['fFlatTripPrice'] = $fFlatTripPrice;
    $Data_trips['fPickUpPrice'] = $fPickUpPrice;
    $Data_trips['fNightPrice'] = $fNightPrice;
    $id = $obj->MySQLQueryPerform("trips", $Data_trips, 'update', $where_trip);

    ## Insert Into trip Destination ###
    $Data_trip_destination['iTripId'] = $iTripId;
    $Data_trip_destination['tDaddress'] = $Address;
    $Data_trip_destination['tEndLat'] = $Latitude;
    $Data_trip_destination['tEndLong'] = $Longitude;
    $Data_trip_destination['tDriverLatitude'] = $data[0]['tDriverLatitude'];
    $Data_trip_destination['tDriverLongitude'] = $data[0]['tDriverLongitude'];
    $Data_trip_destination['eUserType'] = $userType;
    $Data_trip_destination['dAddedDate'] = @date("Y-m-d H:i:s");
    $Data_trip_destination_id = $obj->MySQLQueryPerform('trip_destinations', $Data_trip_destination, 'insert');
    ## Insert Into trip Destination ###

    if ($driverStatus == "Active") {

        $where_passenger = " iUserId = '$userId'";
        $Data_passenger['tDestinationLatitude'] = $Latitude;
        $Data_passenger['tDestinationLongitude'] = $Longitude;
        $Data_passenger['tDestinationAddress'] = $Address;
        $id = $obj->MySQLQueryPerform("register_user", $Data_passenger, 'update', $where_passenger);

    } else {

        /*$ENABLE_PUBNUB = $generalobj->getConfigurations("configurations","ENABLE_PUBNUB");
        $PUBNUB_DISABLED = $generalobj->getConfigurations("configurations","PUBNUB_DISABLED");
        $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations","PUBNUB_PUBLISH_KEY");
        $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations","PUBNUB_SUBSCRIBE_KEY");*/
        if ($PUBNUB_DISABLED == "Yes") {
            $ENABLE_PUBNUB = "No";
        }

        /*if($userType !="Driver"){
        $alertMsg = "Destination is added by passenger.";
        }else{
        $alertMsg = "Destination is added by driver.";
        }  */
        /* For PubNub Setting */
        $tableName = $userType != "Driver" ? "register_driver" : "register_user";
        $iMemberId_VALUE = $userType != "Driver" ? $iDriverId : $userId;
        $iMemberId_KEY = $userType != "Driver" ? "iDriverId" : "iUserId";
        /*$iAppVersion=get_value($tableName, 'iAppVersion', $iMemberId_KEY,$iMemberId_VALUE,'','true');
        $eDeviceType=get_value($tableName, 'eDeviceType', $iMemberId_KEY,$iMemberId_VALUE,'','true');*/
        $AppData = get_value($tableName, 'iAppVersion,eDeviceType,vLang,tSessionId', $iMemberId_KEY, $iMemberId_VALUE);
        $iAppVersion = $AppData[0]['iAppVersion'];
        $eDeviceType = $AppData[0]['eDeviceType'];
        $tSessionId = $AppData[0]['tSessionId'];
        /* For PubNub Setting Finished */
        //$vLangCode=get_value($tableName, 'vLang', $iMemberId_KEY,$iMemberId_VALUE,'','true');
        $vLangCode = $AppData[0]['vLang'];
        if ($vLangCode == "" || $vLangCode == null) {
            $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
        if ($ChangeAddress == "No") {
            $lblValue = $userType == "Driver" ? "LBL_DEST_ADD_BY_DRIVER" : "LBL_DEST_ADD_BY_PASSENGER";
        } else {
            $lblValue = $userType == "Driver" ? "LBL_DEST_EDIT_BY_DRIVER" : "LBL_DEST_EDIT_BY_PASSENGER";
        }
        $alertMsg = $languageLabelsArr[$lblValue];
        $message = "DestinationAdded";
        $message_arr = array();
        $message_arr['Message'] = $message;
        $message_arr['DLatitude'] = $Latitude;
        $message_arr['DLongitude'] = $Longitude;
        $message_arr['DAddress'] = $Address;
        $message_arr['vTitle'] = $alertMsg;
        $message_arr['iTripId'] = $iTripId;
        $message_arr['iDriverId'] = $iDriverId;
        $message_arr['eType'] = $APP_TYPE;
        $message_arr['eFlatTrip'] = $eFlatTrip;
        $message_arr['time'] = strval(time());
        $message = json_encode($message_arr);
        $alertSendAllowed = true;

        #####################Add Status Message#########################
        $DataTripMessages['tMessage'] = $message;
        $DataTripMessages['iDriverId'] = $iDriverId;
        $DataTripMessages['iTripId'] = $iTripId;
        $DataTripMessages['iUserId'] = $userId;
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

        if ($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != "" /*  && $iAppVersion > 1 && $eDeviceType == "Android" */) {

            //$pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);
            $pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY, "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));

            if ($userType != "Driver") {
                $channelName = "DRIVER_" . $iDriverId;
                //$tSessionId=get_value("register_driver", 'tSessionId', "iDriverId",$iDriverId,'','true');
            } else {
                $channelName = "PASSENGER_" . $userId;
                //$tSessionId=get_value("register_user", 'tSessionId', "iUserId",$userId,'','true');
            }
            $message_arr['tSessionId'] = $tSessionId;
            $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);
            $info = $pubnub->publish($channelName, $message_pub);

        }

        $deviceTokens_arr_ios = array();
        $registation_ids_new = array();

        if ($alertSendAllowed == true) {
            if ($data[0]['deviceType'] == "Android" /*&& $ENABLE_PUBNUB != "Yes"*/) {
                array_push($registation_ids_new, $data[0]['regId']);

                $Rmessage = array("message" => $message);

                $result = send_notification($registation_ids_new, $Rmessage, 0);
            } else if ($data[0]['deviceType'] != "Android") {
                array_push($deviceTokens_arr_ios, $data[0]['regId']);

                /*if($ENABLE_PUBNUB == "Yes"){
                $message = "";
                } */

                if ($message != "") {
                    if ($userType == "Driver") {
                        sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
                    } else {
                        sendApplePushNotification(1, $deviceTokens_arr_ios, $message, $alertMsg, 0);
                    }
                }
            }
        }

    }

    $returnArr['Action'] = "1";

} else {
    $returnArr['Action'] = "0";
    $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
}

echo json_encode($returnArr);
