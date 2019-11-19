<?php 
    $iCabBookingId = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : '';
    $scheduleDate = isset($_REQUEST["scheduleDate"]) ? $_REQUEST["scheduleDate"] : '';
    $eConfirmByUser = isset($_REQUEST['eConfirmByUser']) ? $_REQUEST['eConfirmByUser'] : 'No';
    if ($eConfirmByUser == "" || $eConfirmByUser == null) {
        $eConfirmByUser = "No";
    }

    $Booking_Date_Time = $scheduleDate;
    $systemTimeZone = date_default_timezone_get();
    // echo "hererrrrr:::".$systemTimeZone;exit;
    $scheduleDate = converToTz($scheduleDate, $systemTimeZone, $vTimeZone);

    $fPickUpPrice = 1;
    $fNightPrice = 1;
    //$iVehicleTypeId = get_value('cab_booking', 'iVehicleTypeId', 'iCabBookingId',$iCabBookingId,'','true');
    //$iUserId = get_value('cab_booking', 'iUserId', 'iCabBookingId',$iCabBookingId,'','true');
    $sql = "SELECT * from  cab_booking  WHERE iCabBookingId ='" . $iCabBookingId . "'";
    $bookingdetail = $obj->MySQLSelect($sql);
    $iVehicleTypeId = $bookingdetail[0]['iVehicleTypeId'];
    $iUserId = $bookingdetail[0]['iUserId'];
    $vSourceAddresss = $bookingdetail[0]['vSourceAddresss'];
    //added for rental
    $iRentalPackageId = $bookingdetail[0]['iRentalPackageId'];

    $currentdate = date("Y-m-d H:i:s");
    $dBooking_date = $bookingdetail[0]['dBooking_date'];
    $datediff = strtotime($dBooking_date) - strtotime($currentdate);
    if ($datediff < 1800) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_RE_SCHEDULE_BOOK_RESTRICTION";
        echo json_encode($returnArr);exit;
    }

    //added for rental
    $data_surgePrice = checkSurgePrice($iVehicleTypeId, $scheduleDate, $iRentalPackageId);
    if ($data_surgePrice['Action'] == "0") {
        if ($data_surgePrice['message'] == "LBL_PICK_SURGE_NOTE") {
            $fPickUpPrice = $data_surgePrice['SurgePriceValue'];
        } else {
            $fNightPrice = $data_surgePrice['SurgePriceValue'];
        }
        if ($eConfirmByUser == "No") {
            echo json_encode($data_surgePrice);exit;
        }
    }

    $where = " iCabBookingId = '" . $iCabBookingId . "'";
    $Data['fPickUpPrice'] = $fPickUpPrice;
    $Data['fNightPrice'] = $fNightPrice;
    $Data['dBooking_date'] = date('Y-m-d H:i:s', strtotime($scheduleDate));
    $id = $obj->MySQLQueryPerform("cab_booking", $Data, 'update', $where);
    if ($id > 0) {
        $returnArr["Action"] = "1";
        //$returnArr['message']= $APP_TYPE == "Ride" ?"LBL_RIDE_BOOKED":"LBL_DELIVERY_BOOKED";
        $returnArr["message"] = "LBL_INFO_UPDATED_TXT";

        $sql = "SELECT concat(vName,' ',vLastName) as senderName,vEmail,vPhone,vPhoneCode,vLang from  register_user  WHERE iUserId ='" . $iUserId . "'";
        $userdetail = $obj->MySQLSelect($sql);

        $Data1['vRider'] = $userdetail[0]['senderName'];
        $Data1['vRiderMail'] = $userdetail[0]['vEmail'];
        $Data1['vSourceAddresss'] = $vSourceAddresss;
        $Data1['dBookingdate'] = date('Y-m-d H:i', strtotime($Booking_Date_Time));
        $Data1['vBookingNo'] = $bookingdetail[0]['vBookingNo'];

        //$sendMailToAdmin = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_RIDER_RESCEDULE_ADMIN_APP",$Data1);
        //$sendMailToUser = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_RIDER_RESCEDULE_APP",$Data1);

    } else {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);exit;

?>