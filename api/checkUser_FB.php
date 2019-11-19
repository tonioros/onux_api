<?php 

    $fbid = isset($_REQUEST["fbid"]) ? $_REQUEST["fbid"] : '';
    $cityName = isset($_REQUEST["cityName"]) ? $_REQUEST["cityName"] : '';
    $emailId = isset($_REQUEST["emailId"]) ? $_REQUEST["emailId"] : '';
    $GCMID = isset($_REQUEST["GCMID"]) ? $_REQUEST["GCMID"] : '';
    $autoSign = isset($_REQUEST["autoSign"]) ? $_REQUEST["autoSign"] : '';
    $vFirebaseDeviceToken = isset($_REQUEST["vFirebaseDeviceToken"]) ? $_REQUEST["vFirebaseDeviceToken"] : '';

    if ($fbid == '') {
        echo "LBL_NO_REG_FOUND";
        exit;
    }

    $sql = "SELECT iUserId,eStatus,iGcmRegId FROM `register_user` WHERE vFbId=" . $fbid . " OR vEmail='$emailId'";
    $row = $obj->MySQLSelect($sql);

    if (count($row) > 0) {
        if ($row[0]['eStatus'] == "Active") {
            if ($autoSign == "true") {
                $iGCMregID = $row[0]['iGcmRegId'];

                if ($GCMID != '') {

                    if ($iGCMregID != $GCMID) {

                        $iUserID_passenger = $row[0]['iUserId'];
                        $where = " iUserId = '$iUserID_passenger' ";
                        $Data_update_passenger['tSessionId'] = session_id() . time();
                        $Data_update_passenger['iGcmRegId'] = $GCMID;
                        $Data_update_passenger['vFirebaseDeviceToken'] = $vFirebaseDeviceToken;

                        $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);
                    }

                }

            } else {
                if ($GCMID != '') {
                    $iUserId_passenger = $row[0]['iUserId'];
                    $where = " iUserId = '$iUserId_passenger' ";
                    $Data_update_passenger['tSessionId'] = session_id() . time();
                    $Data_update_passenger['iGcmRegId'] = $GCMID;
                    $Data_update_passenger['vFirebaseDeviceToken'] = $vFirebaseDeviceToken;

                    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);
                }
            }

            echo json_encode(getPassengerDetailInfo($row[0]['iUserId'], $cityName));
        } else {
            echo "LBL_CONTACT_US_STATUS_NOTACTIVE_PASSENGER";
        }

    } else {
        echo "LBL_NO_REG_FOUND";
    }

?>