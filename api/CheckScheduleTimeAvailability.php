<?php 
    global $generalobj, $tconfig;
    $scheduleDate = isset($_REQUEST["scheduleDate"]) ? $_REQUEST["scheduleDate"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $systemTimeZone = date_default_timezone_get();
    // echo "hererrrrr:::".$systemTimeZone;exit;
    $currentdate = date("Y-m-d H:i:s");
    $currentdate = converToTz($currentdate, $vTimeZone, $systemTimeZone);
    $sdate = explode(" ", $scheduleDate);
    $shour = explode("-", $sdate[1]);
    $shour1 = $shour[0];
    $shour2 = $shour[1];
    if ($shour1 == "12" && $shour2 == "01") {
        $shour1 = 00;
    }
    $scheduleDate = $sdate[0] . " " . $shour1 . ":00:00";
    $datediff = strtotime($scheduleDate) - strtotime($currentdate);
    if ($datediff > 3600) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_SCHEDULE_TIME_NOT_AVAILABLE";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);
    exit;

?>