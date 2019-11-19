<?php 

    global $generalobj, $obj;
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $iTripTimeId = isset($_REQUEST["iTripTimeId"]) ? $_REQUEST["iTripTimeId"] : '';
    $dTime = date('Y-m-d H:i:s');

    if ($iTripTimeId == '') {
        $Data_update['dResumeTime'] = $dTime;
        $Data_update['iTripId'] = $iTripId;
        $id = $obj->MySQLQueryPerform("trip_times", $Data_update, 'insert');
        $returnArr['Action'] = "1";
        $returnArr['message'] = $id;
    } else {
        $where = " iTripTimeId = '$iTripTimeId'";
        $Data_update['dPauseTime'] = $dTime;
        $Data_update['iTripId'] = $iTripId;
        $id = $obj->MySQLQueryPerform("trip_times", $Data_update, 'update', $where);
        $returnArr['Action'] = "1";
        $returnArr['message'] = $id;
    }
    $sql22 = "SELECT * FROM `trip_times` WHERE iTripId='$iTripId'";
    $db_tripTimes = $obj->MySQLSelect($sql22);

    $totalSec = 0;
    $timeState = 'Pause';
    $iTripTimeId = '';
    foreach ($db_tripTimes as $dtT) {
        if ($dtT['dPauseTime'] != '' && $dtT['dPauseTime'] != '0000-00-00 00:00:00') {
            $totalSec += strtotime($dtT['dPauseTime']) - strtotime($dtT['dResumeTime']);
        } else {
            $totalSec += strtotime(date('Y-m-d H:i:s')) - strtotime($dtT['dResumeTime']);
        }
    }
    $returnArr['totalTime'] = $totalSec;
    $obj->MySQLClose();
    echo json_encode($returnArr);exit;

?>