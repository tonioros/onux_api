<?php 
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $PickUpLatitude = isset($_REQUEST["PickUpLatitude"]) ? $_REQUEST["PickUpLatitude"] : '0.0';
    $PickUpLongitude = isset($_REQUEST["PickUpLongitude"]) ? $_REQUEST["PickUpLongitude"] : '0.0';
    $DestLatitude = isset($_REQUEST["DestLatitude"]) ? $_REQUEST["DestLatitude"] : '';
    $DestLongitude = isset($_REQUEST["DestLongitude"]) ? $_REQUEST["DestLongitude"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $CheckType = isset($_REQUEST["CheckType"]) ? $_REQUEST["CheckType"] : 'Pickup'; // Pickup Or Drop
    if ($CheckType == "" || $CheckType == null) {
        $CheckType = "Pickup";
    }
    $pickuplocationarr = array($PickUpLatitude, $PickUpLongitude);
    $allowed_ans = checkAllowedAreaNew($pickuplocationarr, "No");
    $dropofflocationarr = array($DestLatitude, $DestLongitude);
    $allowed_ans_drop = checkAllowedAreaNew($dropofflocationarr, "Yes");
    $returnArr['Action'] = "1";
    if ($allowed_ans == "No" && $allowed_ans_drop == "No") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_PICK_DROP_LOCATION_NOT_ALLOW";
        echo json_encode($returnArr);
        exit;
    }
    if ($allowed_ans == "Yes" && $allowed_ans_drop == "No") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_DROP_LOCATION_NOT_ALLOW";
        echo json_encode($returnArr);
        exit;
    }
    if ($allowed_ans == "No" && $allowed_ans_drop == "Yes") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_PICKUP_LOCATION_NOT_ALLOW";
        echo json_encode($returnArr);
        exit;
    }
    echo json_encode($returnArr);exit;

?>