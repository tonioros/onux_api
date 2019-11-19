<?php 
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger'; //Passenger,Driver
    $vPaymayaToken = isset($_REQUEST["vPaymayaToken"]) ? $_REQUEST["vPaymayaToken"] : '';
    if ($eMemberType == "Passenger") {
        $tbl_name = "register_user";
        $vEmail = "vEmail";
        $iMemberId = "iUserId";
        $eUserType = "Rider";
    } else {
        $tbl_name = "register_driver";
        $vEmail = "vEmail";
        $iMemberId = "iDriverId";
        $eUserType = "Driver";
    }

    $where = " $iMemberId = '$iUserId'";
    //$updateData['vStripeToken']=$vStripeToken;
    $updateData['vPaymayaToken'] = $vPaymayaToken;

    $id = $obj->MySQLQueryPerform($tbl_name, $updateData, 'update', $where);
    if ($eMemberType == "Passenger") {
        $profileData = getPassengerDetailInfo($iUserId);
    } else {
        $profileData = getDriverDetailInfo($iUserId);
    }

    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $profileData;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    echo json_encode($returnArr);exit;

?>