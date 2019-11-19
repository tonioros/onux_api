<?php 
    global $generalobj, $tconfig;
    $iDriverTimingId = isset($_REQUEST['iDriverTimingId']) ? $_REQUEST['iDriverTimingId'] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $vAvailableTimes = isset($_REQUEST["vAvailableTimes"]) ? $_REQUEST["vAvailableTimes"] : ''; // 4-5,5-6,7-8,11-12,14-15
    $scheduleDate = isset($_REQUEST["scheduleDate"]) ? $_REQUEST["scheduleDate"] : ''; // 2017-10-18
    $eStatus = isset($_REQUEST["eStatus"]) ? $_REQUEST["eStatus"] : 'Active';
    $vDay = date('l', strtotime($scheduleDate));
    $dAddedDate = @date("Y-m-d H:i:s");
    $vAvailableTimes = CheckAvailableTimes($vAvailableTimes); // Convert to 04-05,05-06,07-08,11-12,14-15
    $action = ($iDriverTimingId != '') ? 'Edit' : 'Add';
    $Data_Update_Timing['iDriverId'] = $iDriverId;
    $Data_Update_Timing['vDay'] = $vDay;
    $Data_Update_Timing['vAvailableTimes'] = $vAvailableTimes;
    $Data_Update_Timing['dAddedDate'] = $dAddedDate;
    $Data_Update_Timing['eStatus'] = $eStatus;
    if ($action == "Add") {
        $insertid = $obj->MySQLQueryPerform("driver_manage_timing", $Data_Update_Timing, 'insert');
    } else {
        $where = " iDriverTimingId = '" . $iDriverTimingId . "'";
        $insertid = $obj->MySQLQueryPerform("driver_manage_timing", $Data_Update_Timing, 'update', $where);
    }
    if ($insertid > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = getDriverDetailInfo($iDriverId);
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);
    exit;

?>