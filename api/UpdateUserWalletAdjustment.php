<?php 
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Passenger';
    $eWalletAdjustment = isset($_REQUEST['eWalletAdjustment']) ? $_REQUEST['eWalletAdjustment'] : 'Yes'; // Yes Or No
    if ($userType == "Passenger") {
        $tblname = "register_user";
        $fields = 'iUserId, vPhone,vPhoneCode as vPhoneCode, vEmail, vName, vLastName';
        $condfield = 'iUserId';
    } else {
        $tblname = "register_driver";
        $fields = 'iDriverId, vPhone,vCode as vPhoneCode, vEmail, vName, vLastName';
        $condfield = 'iDriverId';
    }
    $where = " " . $condfield . " = '" . $iMemberId . "'";
    $Data['eWalletAdjustment'] = $eWalletAdjustment;
    $id = $obj->MySQLQueryPerform($tblname, $Data, 'update', $where);
    if ($id) {
        $returnArr['Action'] = "1";
        if ($userType != "Driver") {
            $returnArr['message'] = getPassengerDetailInfo($iMemberId, "");
        } else {
            $returnArr['message'] = getDriverDetailInfo($iMemberId);
        }
        //$returnArr['message']  = "LBL_INFO_UPDATED_TXT_MY_PROFILE";
        $obj->MySQLClose();
        echo json_encode($returnArr);exit;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_EMAIl_VERIFIED_ERROR";
        $obj->MySQLClose();
        echo json_encode($returnArr);exit;
    }

?>