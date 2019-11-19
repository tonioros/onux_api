<?php 

    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $vLatitude = isset($_REQUEST["vLatitude"]) ? $_REQUEST["vLatitude"] : '';
    $vLongitude = isset($_REQUEST["vLongitude"]) ? $_REQUEST["vLongitude"] : '';
    $isSubsToCabReq = isset($_REQUEST["isSubsToCabReq"]) ? $_REQUEST["isSubsToCabReq"] : '';
    //$APP_TYPE = $generalobj->getConfigurations("configurations", "APP_TYPE");
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';

    if ($iMemberId != "") {
        if (!empty($isSubsToCabReq) && $isSubsToCabReq == 'true') {
            $driver_update['tLastOnline'] = date('Y-m-d H:i:s');
            $driver_update['tOnline'] = date('Y-m-d H:i:s');
        }

        if (!empty($vLatitude) && !empty($vLongitude)) {
            $driver_update['vLatitude'] = $vLatitude;
            $driver_update['vLongitude'] = $vLongitude;
            $user_update['vLatitude'] = $vLatitude;
            $user_update['vLongitude'] = $vLongitude;
        }

        if ($isSubsToCabReq == 'true' || !empty($vLatitude) || !empty($vLongitude)) {
            if ($userType == "Driver") {
                $where = " iDriverId = '" . $iMemberId . "'";
                $Update_driver = $obj->MySQLQueryPerform("register_driver", $driver_update, "update", $where);
            } else {
                $where = " iUserId = '" . $iMemberId . "'";
                $Update_driver = $obj->MySQLQueryPerform("register_user", $user_update, "update", $where);
            }
        }
    }
    # Update User Location Date #
    Updateuserlocationdatetime($iMemberId, $userType, $vTimeZone);
    # Update User Location Date #

    if ($userType == "Passenger") {
        $condfield = 'iUserId';
        if ($iTripId != "") {
            $sql = "SELECT t.*, CONCAT(rd.vName,' ',rd.vLastName) AS driverName, rd.vTripStatus, rd.iDriverId, rd.iAppVersion FROM trips AS t LEFT JOIN register_driver AS rd ON rd.iDriverId=t.iDriverId WHERE t.iTripId='" . $iTripId . "'";
            $msg = $obj->MySQLSelect($sql);

            if (!empty($msg)) {
                if ($msg[0]['iActive'] == 'Active') {
                    $DriverMessage = "CabRequestAccepted";

                    $message_arr = array();
                    $message_arr['iDriverId'] = $msg[0]['iDriverId'];
                    $message_arr['Message'] = $DriverMessage;
                    $message_arr['iTripId'] = strval($msg[0]['iTripId']);
                    $message_arr['DriverAppVersion'] = strval($msg[0]['iAppVersion']);
                    $message_arr['iTripVerificationCode'] = $msg[0]['iVerificationCode'];

                    $returnArr['Action'] = "1";
                    $returnArr['message'] = $message_arr;
                } else if ($msg[0]['iActive'] == 'Canceled' && $msg[0]['eCancelledBy'] == 'Driver') {
                    $message = "TripCancelledByDriver";
                    $message_arr = array();
                    $message_arr['Message'] = $message;
                    $message_arr['Reason'] = $msg[0]['vCancelReason'];
                    $message_arr['isTripStarted'] = "false";
                    $message_arr['iUserId'] = $msg[0]['iUserId'];
                    $message_arr['driverName'] = $msg[0]['driverName'];
                    $message_arr['vRideNo'] = $msg[0]['vRideNo'];

                    $returnArr['Action'] = "1";
                    $returnArr['message'] = $message_arr;
                } else if ($msg[0]['vTripStatus'] == 'Arrived') {
                    $message_arr = array();
                    $message_arr['Message'] = "DriverArrived";
                    $message_arr['MsgType'] = "DriverArrived";
                    $message_arr['iDriverId'] = $msg[0]['iDriverId'];
                    $message_arr['driverName'] = $msg[0]['driverName'];
                    $message_arr['vRideNo'] = $msg[0]['vRideNo'];

                    $returnArr['Action'] = "1";
                    $returnArr['message'] = $message_arr;

                } else if ($msg[0]['iActive'] == 'On Going Trip') {
                    $message = "TripStarted";
                    $message_arr = array();
                    $message_arr['Message'] = $message;
                    $message_arr['iDriverId'] = $msg[0]['iDriverId'];
                    $message_arr['driverName'] = $msg[0]['driverName'];
                    $message_arr['vRideNo'] = $msg[0]['vRideNo'];
                    if ($msg[0]['eType'] == "Deliver") {
                        $message_arr['VerificationCode'] = $msg[0]['vDeliveryConfirmCode'];
                    } else {
                        $message_arr['VerificationCode'] = "";
                    }

                    $returnArr['Action'] = "1";
                    $returnArr['message'] = $message_arr;
                } else if ($msg[0]['iActive'] == 'Finished') {
                    $message_arr = array();
                    if ($msg[0]['eCancelled'] == "true") {
                        $message = "TripCancelledByDriver";
                        $message_arr['Reason'] = $msg[0]['vCancelReason'];
                        $message_arr['isTripStarted'] = "true";
                    } else {
                        $message = "TripEnd";
                    }
                    $message_arr['Message'] = $message;
                    $message_arr['iDriverId'] = $msg[0]['iDriverId'];
                    $message_arr['driverName'] = $msg[0]['driverName'];
                    $message_arr['vRideNo'] = $msg[0]['vRideNo'];

                    $returnArr['Action'] = "1";
                    $returnArr['message'] = $message_arr;
                }
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_NO_TRIP_FOUND";
            }
        } else {
            $sql = "SELECT t.*, CONCAT(rd.vName,' ',rd.vLastName) AS driverName, rd.vTripStatus, rd.iDriverId, rd.iAppVersion FROM trips AS t LEFT JOIN register_driver AS rd ON rd.iDriverId=t.iDriverId WHERE t.iUserId='" . $iMemberId . "' ORDER BY t.iTripId DESC limit 1";
            $msg = $obj->MySQLSelect($sql);

            if (!empty($msg)) {

                // Cab Accepted MEssage
                $DriverMessage = "CabRequestAccepted";

                $message_arr1 = array();
                $message_arr1['iDriverId'] = $msg[0]['iDriverId'];
                $message_arr1['Message'] = $DriverMessage;
                $message_arr1['iTripId'] = strval($msg[0]['iTripId']);
                $message_arr1['DriverAppVersion'] = strval($msg[0]['iAppVersion']);
                $message_arr1['iTripVerificationCode'] = $msg[0]['iVerificationCode'];
                $returnArr['message']['Accepted'] = $message_arr1;

                // Trip Cancelled Message
                $message = "TripCancelledByDriver";
                $message_arr2 = array();
                $message_arr2['Message'] = $message;
                $message_arr2['Reason'] = $msg[0]['vCancelReason'];
                $message_arr2['isTripStarted'] = "false";
                $message_arr2['iUserId'] = $msg[0]['iUserId'];
                $message_arr2['driverName'] = $msg[0]['driverName'];
                $message_arr2['vRideNo'] = $msg[0]['vRideNo'];
                $returnArr['message']['Cancel'] = $message_arr2;

                // Driver Arrived Message
                $message_arr3 = array();
                $message_arr3['Message'] = "DriverArrived";
                $message_arr3['MsgType'] = "DriverArrived";
                $message_arr3['iDriverId'] = $msg[0]['iDriverId'];
                $message_arr3['driverName'] = $msg[0]['driverName'];
                $message_arr3['vRideNo'] = $msg[0]['vRideNo'];
                $returnArr['message']['Arrived'] = $message_arr3;

                // Trip Started Message
                $message = "TripStarted";
                $message_arr4 = array();
                $message_arr4['Message'] = $message;
                $message_arr4['iDriverId'] = $msg[0]['iDriverId'];
                $message_arr4['driverName'] = $msg[0]['driverName'];
                $message_arr4['vRideNo'] = $msg[0]['vRideNo'];
                if ($msg[0]['eType'] == "Deliver") {
                    $message_arr4['VerificationCode'] = $msg[0]['vDeliveryConfirmCode'];
                } else {
                    $message_arr4['VerificationCode'] = "";
                }
                $returnArr['message']['Started'] = $message_arr4;

                // Trip Finished Message
                $message_arr = array();
                if ($msg[0]['eCancelled'] == "true") {
                    $message = "TripCancelledByDriver";
                    $message_arr5['Reason'] = $msg[0]['vCancelReason'];
                    $message_arr5['isTripStarted'] = "true";
                } else {
                    $message = "TripEnd";
                }
                $message_arr5['Message'] = $message;
                $message_arr5['iDriverId'] = $msg[0]['iDriverId'];
                $message_arr5['driverName'] = $msg[0]['driverName'];
                $message_arr5['vRideNo'] = $msg[0]['vRideNo'];
                $returnArr['message']['TripEnd'] = $message_arr5;

                $returnArr['Action'] = "1";
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_NO_TRIP_FOUND";
            }
        }
    } else {
        if ($iTripId != "") {
            $sql = "SELECT t.iTripId, t.iUserId, t.vRideNo, CONCAT(rd.vName,' ',rd.vLastName) AS driverName FROM trips AS t LEFT JOIN register_driver AS rd ON rd.iDriverId=t.iDriverId WHERE t.iTripId='" . $iTripId . "' AND t.iActive='Canceled' AND t.eCancelledBy='Passenger'";
            $msg = $obj->MySQLSelect($sql);

            if (!empty($msg)) {
                $message = "TripCancelled";
                $message_arr = array();
                $message_arr['Message'] = $message;
                $message_arr['iUserId'] = $msg[0]['iUserId'];
                $message_arr['driverName'] = $msg[0]['driverName'];
                $message_arr['vRideNo'] = $msg[0]['vRideNo'];

                $returnArr['Action'] = "1";
                $returnArr['message'] = $message_arr;
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_NO_TRIP_FOUND";
            }
        } else {
            $sql = "SELECT tMessage as msg FROM passenger_requests WHERE iDriverId='" . $iMemberId . "' ORDER BY iRequestId DESC LIMIT 1 ";
            $msg = $obj->MySQLSelect($sql);

            if (!empty($msg)) {
                $returnArr['Action'] = "1";
                $returnArr['message'] = $msg;
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_NO_TRIP_FOUND";
            }
        }
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);
    exit;

?>