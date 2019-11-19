<?php 
    global $generalobj, $obj;
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $userType = isset($_REQUEST["userType"]) ? $_REQUEST["userType"] : 'Passenger';
    $Data = array();
    if ($iTripId != "") {
        if ($userType != 'Passenger') {
            $sql = "SELECT ru.iUserId,ru.vimgname as riderImage,concat(ru.vName,' ',ru.vLastName) as riderName, ru.vPhoneCode ,ru.vPhone as riderMobile,ru.vTripStatus as driverStatus, ru.vAvgRating as riderRating, tr.* from trips as tr
				LEFT JOIN register_user as ru ON ru.iUserId=tr.iUserId
				WHERE tr.iTripId = '" . $iTripId . "'";
            $dataUser = $obj->MySQLSelect($sql);
            $Data['driverDetails'] = $dataUser[0];
            $iMemberId = get_value('trips', 'iDriverId', 'iTripId', $iTripId, '', 'true');
            $vLangCode = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');
        } else {
            $sql = "SELECT rd.iDriverId,rd.vImage as driverImage,concat(rd.vName,' ',rd.vLastName) as driverName, rd.vCode ,rd.vPhone as driverMobile,rd.vTripStatus as driverStatus, rd.vAvgRating as driverRating, tr.* from trips as tr
				LEFT JOIN register_driver as rd ON rd.iDriverId=tr.iDriverId
				WHERE tr.iTripId = '" . $iTripId . "'";
            $dataUser = $obj->MySQLSelect($sql);
            $Data['driverDetails'] = $dataUser[0];
            $iMemberId = get_value('trips', 'iUserId', 'iTripId', $iTripId, '', 'true');
            $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
        }
        if ($vLangCode == "" || $vLangCode == null) {
            $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
        $lbl_at = $languageLabelsArr['LBL_AT_GENERAL'];
        $lbl_minago = $languageLabelsArr['LBL_MIN_AGO'];
        if ($userType == "Driver") {
            $Driver_Acceprt_Delivery_Request = $languageLabelsArr['LBL_DRIVER1_ACCEPTED_DELIVERY_REQUEST_TXT'];
            $Driver_Arrived_Pick_Location = $languageLabelsArr['LBL_DRIVER1_ARRIVED_PICK_LOCATION_TXT'];
            $Driver_Start_job = $languageLabelsArr['LBL_PROVIDER1_START_JOB_TXT'];
            $Driver_Finished_job = $languageLabelsArr['LBL_PROVIDER1_FINISHED_JOB_TXT'];
        } else {
            $Driver_Acceprt_Delivery_Request = $languageLabelsArr['LBL_DRIVER_ACCEPTED_DELIVERY_REQUEST_TXT'];
            $Driver_Arrived_Pick_Location = $languageLabelsArr['LBL_DRIVER_ARRIVED_PICK_LOCATION_TXT'];
            $Driver_Start_job = $languageLabelsArr['LBL_PROVIDER_START_JOB_TXT'];
            $Driver_Finished_job = $languageLabelsArr['LBL_PROVIDER_FINISHED_JOB_TXT'];
        }

        $testBool = 1;

        if (count($dataUser) > 0) {
            $Data['States'] = array();
            $Data_tTripRequestDate = $dataUser[0]['tTripRequestDate'];
            $Data_tDriverArrivedDate = $dataUser[0]['tDriverArrivedDate'];
            $Data_dDeliveredDate = $dataUser[0]['dDeliveredDate'];
            $Data_tStartDate = $dataUser[0]['tStartDate'];
            $Data_tEndDate = $dataUser[0]['tEndDate'];
            $i = 0;

            if ($Data_tTripRequestDate != "" && $Data_tTripRequestDate != "0000-00-00 00:00:00" && $testBool == 1) {
                $msg = 'Provider accepted the request.';
                if ($userType != 'Passenger') {
                    $msg = 'You accepted the request.';
                }
                $Data['States'][$i]['text'] = $Driver_Acceprt_Delivery_Request;
                $Data['States'][$i]['time'] = date("h:i A", strtotime($Data_tTripRequestDate));
                $Data['States'][$i]['timediff'] = @round(abs(strtotime($Data_tTripRequestDate) - strtotime(date("Y-m-d H:i:s"))) / 60, 0) . " " . $lbl_minago;
                $Data['States'][$i]['type'] = "Accept";
                $i++;
            } else {
                $testBool = 0;
            }

            if ($Data_tDriverArrivedDate != "" && $Data_tDriverArrivedDate != "0000-00-00 00:00:00" && $testBool == 1) {
                $msg = "Provider arrived to your location.";
                if ($userType != 'Passenger') {
                    $msg = "You arrived to user's location.";
                }
                $Data['States'][$i]['text'] = $Driver_Arrived_Pick_Location;
                $Data['States'][$i]['time'] = date("h:i A", strtotime($Data_tDriverArrivedDate));
                $Data['States'][$i]['timediff'] = @round(abs(strtotime($Data_tDriverArrivedDate) - strtotime(date("Y-m-d H:i:s"))) / 60, 0) . " " . $lbl_minago;
                $Data['States'][$i]['type'] = "Arrived";
                $i++;
            } else {
                $testBool = 0;
            }

            if ($Data_tStartDate != "" && $Data_tStartDate != "0000-00-00 00:00:00" && $testBool == 1) {
                $msg = 'Provider has started the job.';
                if ($userType != 'Passenger') {
                    $msg = 'You started the job.';
                }
                $Data['States'][$i]['text'] = $Driver_Start_job;
                $Data['States'][$i]['time'] = date("h:i A", strtotime($Data_tStartDate));
                $Data['States'][$i]['timediff'] = @round(abs(strtotime($Data_tStartDate) - strtotime(date("Y-m-d H:i:s"))) / 60, 0) . " " . $lbl_minago;
                $Data['States'][$i]['type'] = "Onway";
                $i++;
            } else {
                $testBool = 0;
            }

            if ($Data_tEndDate != "" && $Data_tEndDate != "0000-00-00 00:00:00" && $testBool == 1 && $dataUser[0]['iActive'] == "Finished") {
                $msg = 'Provider has completed the job.';
                if ($userType != 'Passenger') {
                    $msg = 'You completed the job.';
                }
                $Data['States'][$i]['text'] = $Driver_Finished_job;
                $Data['States'][$i]['time'] = date("h:i A", strtotime($Data_tEndDate));
                $Data['States'][$i]['timediff'] = @round(abs(strtotime($Data_tEndDate) - strtotime(date("Y-m-d H:i:s"))) / 60, 0) . " " . $lbl_minago;
                $Data['States'][$i]['type'] = "Delivered";
                $i++;
            }
        } else {
            $Data['States'] = array();
        }
        if (count($Data) > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = $Data;
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_NO_DRIVER_FOUND";
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_TRIP_FOUND";
    }
    echo json_encode($returnArr);

?>