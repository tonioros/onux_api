<?php 
    global $generalobj, $tconfig;
    $iDriverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';
    $currDate = date('Y-m-d H:i:s');
    $ssql1 = " AND dBooking_date > '" . $currDate . "'";
    $sql = "select count(iCabBookingId) as Total_Pending from `cab_booking` where iDriverId != '' AND eStatus = 'Pending' AND iDriverId = '" . $iDriverId . "' " . $ssql1 . " ORDER BY iCabBookingId DESC";
    $db_data_pending = $obj->MySQLSelect($sql);
    $sql1 = "select count(iCabBookingId) as Total_Upcoming from `cab_booking` where  iDriverId != '' AND eStatus = 'Accepted' AND iDriverId='" . $iDriverId . "' " . $ssql1 . " ORDER BY iCabBookingId DESC";
    $db_data_assign = $obj->MySQLSelect($sql1);
    $sql2 = "SELECT vWorkLocationRadius as Radius FROM register_driver where iDriverId = '" . $iDriverId . "' ORDER BY iDriverId DESC ";
    $db_data_radius = $obj->MySQLSelect($sql2);
    // $radius = ($db_data_radius[0] != "") ?  $db_data_radius[0] : array("Radius"=>"0");
    $eUnit = getMemberCountryUnit($iDriverId, "Driver");
    if ($eUnit == "Miles") {
        $db_data_radius[0]['Radius'] = round($db_data_radius[0]['Radius'] * 0.621371);
    }
    $returnArr['Action'] = "1";
    $returnArr['Pending_Count'] = (count($db_data_pending) > 0 && empty($db_data_pending) == false) ? $db_data_pending[0]['Total_Pending'] : 0;
    $returnArr['Upcoming_Count'] = (count($db_data_assign) > 0 && empty($db_data_assign) == false) ? $db_data_assign[0]['Total_Upcoming'] : 0;
    $returnArr['Radius'] = count($db_data_radius) > 0 ? $db_data_radius[0]['Radius'] : 0;
    /* if (count($db_data_pending) > 0 || count($db_data_assign) > 0 || count($db_data_radius) > 0) {
    $returnArr['Action'] = "1";
    $returnArr['Pending_Count'] = $db_data_pending[0]['Total_Pending'];
    $returnArr['Upcoming_Count'] = $db_data_assign[0]['Total_Upcoming'];
    $returnArr['Radius'] = $radius['Radius'];
    } else {
    $returnArr['Action'] = "0";
    $returnArr['Message'] = "LBL_NO_DATA_FOUND";
    } */
    $obj->MySQLClose();
    echo json_encode($returnArr);
    exit;

?>