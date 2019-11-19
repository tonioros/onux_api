<?php 
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
    $iCabBookingId = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : '';
    $Reason = isset($_REQUEST["Reason"]) ? $_REQUEST["Reason"] : '';
    //$APP_TYPE = $generalobj->getConfigurations("configurations","APP_TYPE");

    $where = " iCabBookingId = '$iCabBookingId'";
    $data_update_booking['eStatus'] = "Cancel";
    $data_update_booking['vCancelReason'] = $Reason;
    $data_update_booking['iCancelByUserId'] = $iUserId;
    $data_update_booking['dCancelDate'] = @date("Y-m-d H:i:s");
    $data_update_booking['eCancelBy'] = $userType == "Driver" ? $userType : "Rider";
    $id = $obj->MySQLQueryPerform("cab_booking", $data_update_booking, 'update', $where);

    $sql = "select cb.vBookingNo,concat(rd.vName,' ',rd.vLastName) as DriverName,concat(ru.vName,' ',ru.vLastName) as RiderName,ru.vEmail as vRiderMail,ru.vPhone as RiderPhone,ru.vPhoneCode as RiderPhoneCode,rd.vPhone as DriverPhone,rd.vCode as DriverPhoneCode,rd.vEmail as vDriverMail,rd.vLang as driverlang, ru.vLang as riderlang ,cb.vSourceAddresss,cb.tDestAddress,cb.dBooking_date,cb.vCancelReason,cb.dCancelDate from cab_booking cb
		left join register_driver rd on rd.iDriverId = cb.iDriverId
		left join register_user ru on ru.iUserId = cb.iUserId where cb.iCabBookingId = '$iCabBookingId'";
    $data_cab = $obj->MySQLSelect($sql);

    $RiderPhoneNo = $data_cab[0]['RiderPhone'];
    $RiderPhoneCode = $data_cab[0]['RiderPhoneCode'];
    $UserLang = $data_cab[0]['riderlang'];
    $DriverPhoneNo = $data_cab[0]['DriverPhone'];
    $DriverPhoneCode = $data_cab[0]['DriverPhoneCode'];
    $DriverLang = $data_cab[0]['driverlang'];
    $Data['vBookingNo'] = $data_cab[0]['vBookingNo'];
    $Data['DriverName'] = $data_cab[0]['DriverName'];
    $Data['RiderName'] = $data_cab[0]['RiderName'];
    $Data['vDriverMail'] = $data_cab[0]['vDriverMail'];
    $Data['vRiderMail'] = $data_cab[0]['vRiderMail'];
    $Data['vSourceAddresss'] = $data_cab[0]['vSourceAddresss'];
    $Data['tDestAddress'] = $data_cab[0]['tDestAddress'];
    $Data['dBookingdate'] = date('Y-m-d H:i', strtotime($data_cab[0]['dBooking_date']));
    $Data['vCancelReason'] = $Reason;
    $Data['dCancelDate'] = $data_cab[0]['dCancelDate'];

    if ($userType == "Driver") {
        $generalobj->send_email_user("MANUAL_CANCEL_TRIP_ADMIN", $Data);
    }
    if ($APP_TYPE == "UberX") {
        $USER_EMAIL_TEMPLATE = ($userType == "Driver") ? "MANUAL_BOOKING_CANCEL_BYDRIVER_SP" : "MANUAL_BOOKING_CANCEL_BYRIDER_SP";
        $generalobj->send_email_user($USER_EMAIL_TEMPLATE, $Data);
        $UserPhoneNo = ($userType == "Driver") ? $RiderPhoneNo : $DriverPhoneNo;
        $UserPhoneCode = ($userType == "Driver") ? $RiderPhoneCode : $DriverPhoneCode;
        $USER_SMS_TEMPLATE = ($userType == "Driver") ? "BOOKING_CANCEL_BYDRIVER_MESSAGE_SP" : "BOOKING_CANCEL_BYRIDER_MESSAGE_SP";
        $message_layout = $generalobj->send_messages_user($USER_SMS_TEMPLATE, $Data, "", $UserLang);
        $UsersendMessage = $generalobj->sendUserSMS($UserPhoneNo, $UserPhoneCode, $message_layout, "");
        if ($UsersendMessage == 0) {
            //$isdCode= $generalobj->getConfigurations("configurations","SITE_ISD_CODE");
            $isdCode = $SITE_ISD_CODE;
            $UserPhoneCode = $isdCode;
            $UsersendMessage = $generalobj->sendUserSMS($UserPhoneNo, $UserPhoneCode, $message_layout, "");
        }
    }

    if ($id) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_BOOKING_CANCELED";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    echo json_encode($returnArr);
?>