<?php 
    global $generalobj, $obj;

    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '0';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '0';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';

    $sql = "SELECT * FROM user_emergency_contact WHERE iUserId = '" . $iUserId . "' AND eUserType='" . $UserType . "'";

    $dataArr = $obj->MySQLSelect($sql);
    if ($iTripId == "" || $iTripId == "0") {
        $tableName = $UserType != "Driver" ? "register_user" : "register_driver";
        $iMemberId_KEY = $UserType != "Driver" ? "iUserId" : "iDriverId";
        $iTripId = get_value($tableName, 'iTripId', $iMemberId_KEY, $iUserId, '', 'true');
    }

    if (count($dataArr) > 0) {
        $sql = "SELECT tr.*,dv.vLicencePlate,CONCAT(rd.vName,' ',rd.vLastName) as vDriverName,rd.vPhone as DriverPhone,CONCAT(ru.vName,' ',ru.vLastName) as vPassengerName,ru.vPhone as PassengerPhone FROM trips as tr, register_driver as rd, register_user as ru, driver_vehicle as dv WHERE tr.iTripId = '" . $iTripId . "' AND rd.iDriverId = tr.iDriverId AND ru.iUserId = tr.iUserId AND dv.iDriverVehicleId = tr.iDriverVehicleId";

        $tripData = $obj->MySQLSelect($sql);
        //$tripData[0]['tStartDate'] = ($tripData[0]['tStartDate'] == '0000-00-00 00:00:00')? $tripData[0]['tTripRequestDate'] : $tripData[0]['tStartDate'];
        $tStartDate = ($tripData[0]['tStartDate'] == '0000-00-00 00:00:00') ? $tripData[0]['tTripRequestDate'] : $tripData[0]['tStartDate'];
        $systemTimeZone = date_default_timezone_get();
        $vTimeZone = $tripData[0]['vTimeZone'];
        $tStartDate = converToTz($tStartDate, $vTimeZone, $systemTimeZone);
        $tripData[0]['tStartDate'] = $tStartDate;
        $tTripRequestDate = $tripData[0]['tTripRequestDate'];
        $tTripRequestDate = converToTz($tTripRequestDate, $vTimeZone, $systemTimeZone);
        $tripData[0]['tTripRequestDate'] = $tTripRequestDate;
        $eType = $tripData[0]['eType'];
        //$isdCode= $generalobj->getConfigurations("configurations","SITE_ISD_CODE");
        $isdCode = $SITE_ISD_CODE;

        //if($APP_TYPE == "UberX"){
        if ($eType == "UberX") {
            if ($UserType == "Passenger") {
                $message = "Important: " . $tripData[0]['vPassengerName'] . ' (' . $tripData[0]['PassengerPhone'] . ') has reached out to you via ' . $SITE_NAME . ' SOS. Please reach out to him/her urgently. The details of the Job are: Job start time: ' . date('dS M \a\t h:i a', strtotime($tripData[0]['tTripRequestDate'])) . '. Job Address: ' . $tripData[0]['tSaddress'] . '. Service Provider name: ' . $tripData[0]['vDriverName'] . '. Service Provider number:(' . $tripData[0]['DriverPhone'] . ")";
            } else {
                $message = "Important: " . $tripData[0]['vDriverName'] . ' (' . $tripData[0]['DriverPhone'] . ') has reached out to you via ' . $SITE_NAME . ' SOS. Please reach out to him/her urgently. The details of the Job are: Job start time: ' . date('dS M Y \a\t h:i a', strtotime($tripData[0]['tStartDate'])) . '. Job Address: ' . $tripData[0]['tSaddress'] . '. User name: ' . $tripData[0]['vPassengerName'] . '. User number:(' . $tripData[0]['PassengerPhone'] . ")";
            }
        } else if ($eType == "Deliver") {
            if ($UserType == "Passenger") {
                $message = "Important: " . $tripData[0]['vPassengerName'] . ' (' . $tripData[0]['PassengerPhone'] . ') has reached out to you via ' . $SITE_NAME . ' SOS. Please reach out to him/her urgently. The details of the delivery are: Delivery start time: ' . date('dS M Y \a\t h:i a', strtotime($tripData[0]['tStartDate'])) . '. Pick up from: ' . $tripData[0]['tSaddress'] . '. Delivery Driver name: ' . $tripData[0]['vDriverName'] . '. Delivery Driver number:(' . $tripData[0]['DriverPhone'] . "). Delivery Driver's car number: " . $tripData[0]['vLicencePlate'];
            } else {
                $message = "Important: " . $tripData[0]['vDriverName'] . ' (' . $tripData[0]['DriverPhone'] . ') has reached out to you via ' . $SITE_NAME . ' SOS. Please reach out to him/her urgently. The details of the delivery are: Delivery start time: ' . date('dS M Y \a\t h:i a', strtotime($tripData[0]['tStartDate'])) . '. Pick up from: ' . $tripData[0]['tSaddress'] . '. Sender name: ' . $tripData[0]['vPassengerName'] . '. Sender number:(' . $tripData[0]['PassengerPhone'] . "). Delivery Driver's car number: " . $tripData[0]['vLicencePlate'];
            }
        } else {
            if ($UserType == "Passenger") {
                $message = "Important: " . $tripData[0]['vPassengerName'] . ' (' . $tripData[0]['PassengerPhone'] . ') has reached out to you via ' . $SITE_NAME . ' SOS. Please reach out to him/her urgently. The details of the ride are: Trip start time: ' . date('dS M Y \a\t h:i a', strtotime($tripData[0]['tStartDate'])) . '. Pick up from: ' . $tripData[0]['tSaddress'] . '. Driver name: ' . $tripData[0]['vDriverName'] . '. Driver number:(' . $tripData[0]['DriverPhone'] . "). Driver's car number: " . $tripData[0]['vLicencePlate'];
            } else {
                $message = "Important: " . $tripData[0]['vDriverName'] . ' (' . $tripData[0]['DriverPhone'] . ') has reached out to you via ' . $SITE_NAME . ' SOS. Please reach out to him/her urgently. The details of the ride are: Trip start time: ' . date('dS M Y \a\t h:i a', strtotime($tripData[0]['tStartDate'])) . '. Pick up from: ' . $tripData[0]['tSaddress'] . '. Passenger name: ' . $tripData[0]['vPassengerName'] . '. Passenger number:(' . $tripData[0]['PassengerPhone'] . "). Driver's car number: " . $tripData[0]['vLicencePlate'];
            }
        }

        for ($i = 0; $i < count($dataArr); $i++) {
            $phone = preg_replace("/[^0-9]/", "", $dataArr[$i]['vPhone']);

            $toMobileNum = "+" . $phone;

            $result = sendEmeSms($toMobileNum, $message);
            if ($result == 0) {
                $toMobileNum = "+" . $isdCode . $phone;
                sendEmeSms($toMobileNum, $message);
            }
        }

        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_EME_CONTACT_ALERT_SENT";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_ADD_EME_CONTACTS";
    }

    echo json_encode($returnArr);

?>