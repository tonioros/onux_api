<?php 

    $iCabBookingId = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : '';
    $eStatus = isset($_REQUEST["eStatus"]) ? $_REQUEST["eStatus"] : '';
    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
    $vCancelReason = isset($_REQUEST['vCancelReason']) ? $_REQUEST['vCancelReason'] : '';
    $eConfirmByProvider = isset($_REQUEST['eConfirmByProvider']) ? $_REQUEST['eConfirmByProvider'] : 'No';
    $dataType = isset($_REQUEST["DataType"]) ? $_REQUEST["DataType"] : '';
    if ($eConfirmByProvider == "" || $eConfirmByProvider == null) {
        $eConfirmByProvider = "No";
    }

    ############################################################### CheckPendingBooking UBERX  For same Time booking (Accept , Pending)###########################################################
    if ($APP_TYPE == "UberX") {
        $sql_book = "SELECT dBooking_date from cab_booking WHERE iCabBookingId ='" . $iCabBookingId . "'";
        $checkbooking = $obj->MySQLSelect($sql_book);
        $dBooking_date = $checkbooking[0]['dBooking_date'];
        $sql = "SELECT iCabBookingId from cab_booking WHERE iDriverId ='" . $iDriverId . "' AND dBooking_date = '" . $dBooking_date . "' AND eStatus = 'Accepted' AND iCabBookingId != '" . $iCabBookingId . "'";
        $pendingacceptdriverbooking = $obj->MySQLSelect($sql);
        if (count($pendingacceptdriverbooking) > 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_PENDING_PLUS_ACCEPT_BOOKING_AVAIL_TXT";
            $returnArr['message1'] = "Accept";
            echo json_encode($returnArr);exit;
        } else {
            $sql = "SELECT iCabBookingId from cab_booking WHERE iDriverId ='" . $iDriverId . "' AND dBooking_date = '" . $dBooking_date . "' AND eStatus = 'Pending' AND iCabBookingId != '" . $iCabBookingId . "'";
            $pendingdriverbooking = $obj->MySQLSelect($sql);
            if (count($pendingdriverbooking) > 0 && $eConfirmByProvider == "No") {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_PENDING_BOOKING_AVAIL_TXT";
                $returnArr['message1'] = "Pending";
                $returnArr['BookingFound'] = "Yes";
                echo json_encode($returnArr);exit;
            }
        }
    }
    ############################################################### CheckPendingBooking UBERX ###########################################################

    ### Checking For booking timing availablity when driver accept booking ###
    if ($eConfirmByProvider == "No" && $eStatus == "Accepted" && $APP_TYPE == "UberX") {
        $sql = "SELECT dBooking_date from cab_booking WHERE iCabBookingId ='" . $iCabBookingId . "'";
        $bookingdate = $obj->MySQLSelect($sql);
        $dBooking_date = $bookingdate[0]['dBooking_date'];
        $additional_mins = $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL;
        $FromDate = date("Y-m-d H:i:s", strtotime($dBooking_date . "-" . $additional_mins . " minutes"));
        $ToDate = date("Y-m-d H:i:s", strtotime($dBooking_date . "+" . $additional_mins . " minutes"));
        $sql = "SELECT iCabBookingId from cab_booking WHERE (dBooking_date BETWEEN '" . $FromDate . "' AND '" . $ToDate . "') AND iCabBookingId != '" . $iCabBookingId . "' AND eStatus = 'Accepted' AND iDriverId = '" . $iDriverId . "'";
        $checkbookingdate = $obj->MySQLSelect($sql);
        if (count($checkbookingdate) > 0) {
            $returnArr['Action'] = "0";
            $returnArr['BookingFound'] = "Yes";
            $returnArr['message'] = "LBL_PROVIDER_JOB_FOUND_TXT";
            echo json_encode($returnArr);exit;
        }
    }
    ### Checking For booking timing availablity when driver accept booking ###

    $where = " iCabBookingId = '$iCabBookingId' ";
    $Data['eStatus'] = $eStatus;
    $Data['vCancelReason'] = $vCancelReason;
    $Update_Booking_id = $obj->MySQLQueryPerform("cab_booking", $Data, 'update', $where);
    if ($Update_Booking_id) {

        $sql = "SELECT cb.*,concat(ru.vName,' ',ru.vLastName) as UserName,ru.vEmail,ru.vPhone,ru.vPhoneCode,ru.vLang as userlang,concat(rd.vName,' ',rd.vLastName) as DriverName from cab_booking as cb LEFT JOIN register_user as ru ON ru.iUserId=cb.iUserId LEFT JOIN register_driver as rd ON rd.iDriverId=cb.iDriverId WHERE cb.iCabBookingId ='" . $iCabBookingId . "'";
        $bookingdetail = $obj->MySQLSelect($sql);
        $UserPhoneNo = $bookingdetail[0]['vPhone'];
        $UserPhoneCode = $bookingdetail[0]['vPhoneCode'];
        $UserLang = $bookingdetail[0]['userlang'];
        $Data1['vRider'] = $bookingdetail[0]['UserName'];
        $Data1['vDriver'] = $bookingdetail[0]['DriverName'];
        $Data1['vRiderMail'] = $bookingdetail[0]['vEmail'];
        $Data1['vBookingNo'] = $bookingdetail[0]['vBookingNo'];
        $Data1['dBookingdate'] = date('Y-m-d H:i', strtotime($bookingdetail[0]['dBooking_date']));
        if ($eStatus == "Accepted") {
            $returnArr['message'] = "LBL_JOB_ACCEPTED";
            $sendMailtoUser = $generalobj->send_email_user("MANUAL_BOOKING_ACCEPT_BYDRIVER_SP", $Data1);
        } else if ($eStatus == "Declined") {
            $returnArr['message'] = "LBL_JOB_DECLINED";
            $sendMailtoUser = $generalobj->send_email_user("MANUAL_BOOKING_DECLINED_BYDRIVER_SP", $Data1);
        } else {
            $returnArr['message'] = getDriverDetailInfo($iDriverId);
        }
        if ($eStatus == "Accepted" || $eStatus == "Declined") {
            $USER_SMS_TEMPLATE = ($eStatus == "Accepted") ? "BOOKING_ACCEPT_BYDRIVER_MESSAGE_SP" : "BOOKING_DECLINED_BYDRIVER_MESSAGE_SP";
            $message_layout = $generalobj->send_messages_user($USER_SMS_TEMPLATE, $Data1, "", $UserLang);
            $UsersendMessage = $generalobj->sendUserSMS($UserPhoneNo, $UserPhoneCode, $message_layout, "");
            if ($UsersendMessage == 0) {
                $isdCode = $SITE_ISD_CODE;
                $UserPhoneCode = $isdCode;
                $UsersendMessage = $generalobj->sendUserSMS($UserPhoneNo, $UserPhoneCode, $message_layout, "");
            }
        }
        $returnArr['Action'] = "1";
        if ($eStatus == "Accepted") {
            $returnArr['message'] = "LBL_JOB_ACCEPTED";
        } else if ($eStatus == "Declined" && $dataType == "PENDING") {
            $returnArr['message'] = "LBL_JOB_DECLINED";
        } else if ($eStatus == "Declined" && $dataType != "PENDING") {
            $returnArr['message'] = "LBL_BOOKING_CANCELED";
        } else {
            $returnArr['message'] = getDriverDetailInfo($iDriverId);
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);exit;

?>