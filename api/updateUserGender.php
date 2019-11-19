<?php 
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Driver';
    $eGender = isset($_REQUEST['eGender']) ? clean($_REQUEST['eGender']) : '';

    if ($userType == "Driver") {
        $where = " iDriverId = '$iMemberId'";
        $Data_update_User['eGender'] = $eGender;

        $id = $obj->MySQLQueryPerform("register_driver", $Data_update_User, 'update', $where);
    } else {
        $where = " iUserId = '$iMemberId'";
        $Data_update_User['eGender'] = $eGender;

        $id = $obj->MySQLQueryPerform("register_user", $Data_update_User, 'update', $where);
    }

    if ($id > 0) {
        $returnArr['Action'] = "1";
        if ($userType != "Driver") {
            $returnArr['message'] = getPassengerDetailInfo($iMemberId, "");
        } else {
            $returnArr['message'] = getDriverDetailInfo($iMemberId);
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    echo json_encode($returnArr);

?>