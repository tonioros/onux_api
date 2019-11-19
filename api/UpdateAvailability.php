<?php 
    global $generalobj, $tconfig;
    $iDriverTimingId = isset($_REQUEST['iDriverTimingId']) ? $_REQUEST['iDriverTimingId'] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $vDay = isset($_REQUEST["vDay"]) ? $_REQUEST["vDay"] : '';
    $vAvailableTimes = isset($_REQUEST["vAvailableTimes"]) ? $_REQUEST["vAvailableTimes"] : '';
    $eStatus = isset($_REQUEST["eStatus"]) ? $_REQUEST["eStatus"] : 'Active';
    $dAddedDate = @date("Y-m-d H:i:s");
    $vAvailableTimes = CheckAvailableTimes($vAvailableTimes); // Convert to 04-05,05-06,07-08,11-12,14-15
    $sql = "select iDriverTimingId from `driver_manage_timing` where iDriverId = '" . $iDriverId . "' AND vDay LIKE '" . $vDay . "'";
    $db_data = $obj->MySQLSelect($sql);
    //$action = ($iDriverTimingId != '')?'Edit':'Add';
    if (count($db_data) > 0) {
        $action = "Edit";
        $iDriverTimingId = $db_data[0]['iDriverTimingId'];
    } else {
        $action = "Add";
    }
    $Data_driver_timing['iDriverId'] = $iDriverId;
    $Data_driver_timing['vDay'] = $vDay;
    $Data_driver_timing['vAvailableTimes'] = $vAvailableTimes;
    $Data_driver_timing['dAddedDate'] = $dAddedDate;
    $Data_driver_timing['eStatus'] = $eStatus;
    if ($action == "Add") {
        $insertid = $obj->MySQLQueryPerform("driver_manage_timing", $Data_driver_timing, 'insert');
        $TimingId = $insertid;
    } else {
        $where = " iDriverTimingId = '" . $iDriverTimingId . "'";
        $insertid = $obj->MySQLQueryPerform("driver_manage_timing", $Data_driver_timing, 'update', $where);
        $TimingId = $iDriverTimingId;
    }
    if ($insertid > 0) {
        $returnArr['Action'] = "1";
        $returnArr['TimingId'] = $insertid;
        $returnArr['message'] = "LBL_TIMESLOT_ADD_SUCESS_MSG";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);
    exit;

?>