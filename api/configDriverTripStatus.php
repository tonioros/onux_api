<?php 
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $vLatitude = isset($_REQUEST["vLatitude"]) ? $_REQUEST["vLatitude"] : '';
    $vLongitude = isset($_REQUEST["vLongitude"]) ? $_REQUEST["vLongitude"] : '';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $isSubsToCabReq = isset($_REQUEST["isSubsToCabReq"]) ? $_REQUEST["isSubsToCabReq"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';

    if ($iMemberId != "") {
        if (!empty($isSubsToCabReq) && $isSubsToCabReq == 'true') {
            $driver_update['tLastOnline'] = date('Y-m-d H:i:s');
            $driver_update['tOnline'] = date('Y-m-d H:i:s');
        }

        if (!empty($vLatitude) && !empty($vLongitude)) {
            $driver_update['vLatitude'] = $vLatitude;
            $driver_update['vLongitude'] = $vLongitude;
        }

        if (count($driver_update) > 0) {
            $where = " iDriverId = '" . $iMemberId . "'";
            $Update_driver = $obj->MySQLQueryPerform("register_driver", $driver_update, "update", $where);
            # Update User Location Date #
            Updateuserlocationdatetime($iMemberId, "Driver", $vTimeZone);
            # Update User Location Date #
        }

    }
    if ($iTripId != "") {
        $sql = "SELECT tMessage as msg, iStatusId FROM trip_status_messages WHERE iDriverId='" . $iMemberId . "' AND eToUserType='Driver' AND eReceived='No' ORDER BY iStatusId DESC LIMIT 1 ";
        $msg = $obj->MySQLSelect($sql);
    } else {
        $date = @date("Y-m-d");
        $sql = "SELECT passenger_requests.tMessage as msg  FROM passenger_requests LEFT JOIN driver_request ON  driver_request.iRequestId=passenger_requests.iRequestId  LEFT JOIN register_driver ON register_driver.iDriverId=passenger_requests.iDriverId where date_format(passenger_requests.dAddedDate,'%Y-%m-%d')= '" . $date . "' AND  passenger_requests.iDriverId=" . $iMemberId . " AND driver_request.eStatus='Timeout' AND driver_request.iDriverId='" . $iMemberId . "' AND register_driver.vTripStatus IN ('Not Active','NONE','Cancelled') ORDER BY passenger_requests.iRequestId DESC LIMIT 1 ";
        $msg = $obj->MySQLSelect($sql);
    }

    $returnArr['Action'] = "0";
    if (!empty($msg)) {

        $returnArr['Action'] = "1";

        if ($iTripId != "") {
            //$updateQuery = "UPDATE trip_status_messages SET eReceived = 'Yes' WHERE iStatusId='".$msg[0]['iStatusId']."'";
            $updateQuery = "UPDATE trip_status_messages SET eReceived = 'Yes' WHERE iDriverId='" . $iMemberId . "'";
            $obj->sql_query($updateQuery);

            $returnArr['Action'] = "1";
            $returnArr['message'] = $msg[0]['msg'];
        } else {

            $driver_request['eStatus'] = "Received";
            $where = " iDriverId =" . $iMemberId . " and date_format(tDate,'%Y-%m-%d') = '" . $date . "' AND eStatus = 'Timeout' ";
            $obj->MySQLQueryPerform("driver_request", $driver_request, "update", $where);

            // $updatequery = "update driver_request set eStatus='Received' where iDriverId='".$iMemberId."' AND   date_format(tDate,'%Y-%m-%d') = '" . $date . "'  AND eStatus = 'Timeout'";
            // $obj->sql_query($updateQuery);

            $returnArr['Action'] = "1";
            $dataArr = array();
            for ($i = 0; $i < count($msg); $i++) {
                $dataArr[$i] = $msg[$i]['msg'];
            }
            $returnArr['message'] = $dataArr;
        }

    }
    $obj->MySQLClose();
    echo json_encode($returnArr, JSON_UNESCAPED_UNICODE);
    exit;

?>