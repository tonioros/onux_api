<?php 
    $iCabBookingId = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : '';
    $eStatus = isset($_REQUEST["eStatus"]) ? $_REQUEST["eStatus"] : '';
    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';

    $sql_book = "SELECT dBooking_date from cab_booking WHERE iCabBookingId ='" . $iCabBookingId . "'";
    $checkbooking = $obj->MySQLSelect($sql_book);
    $dBooking_date = $checkbooking[0]['dBooking_date'];

    $sql = "SELECT iCabBookingId from cab_booking WHERE iDriverId ='" . $iDriverId . "' AND dBooking_date = '" . $dBooking_date . "' AND eStatus = 'Accepted' AND iCabBookingId != '" . $iCabBookingId . "'";
    $pendingacceptdriverbooking = $obj->MySQLSelect($sql);

    if (count($pendingacceptdriverbooking) > 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_PENDING_PLUS_ACCEPT_BOOKING_AVAIL_TXT";
        $returnArr['message1'] = "Accept";
    } else {
        $sql = "SELECT iCabBookingId from cab_booking WHERE iDriverId ='" . $iDriverId . "' AND dBooking_date = '" . $dBooking_date . "' AND eStatus = 'Pending' AND iCabBookingId != '" . $iCabBookingId . "'";
        $pendingdriverbooking = $obj->MySQLSelect($sql);
        if (count($pendingdriverbooking) > 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_PENDING_BOOKING_AVAIL_TXT";
            $returnArr['message1'] = "Pending";
        } else {
            $returnArr['Action'] = "1";
        }
    }

    $obj->MySQLClose();
    echo json_encode($returnArr);exit;
?>