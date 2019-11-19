<?php 

    $user_id = isset($_REQUEST["UserID"]) ? $_REQUEST["UserID"] : '';
    $Upass = isset($_REQUEST["pass"]) ? $_REQUEST["pass"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? clean($_REQUEST["UserType"]) : ''; // UserType = Driver/Passenger
    $CurrentPassword = isset($_REQUEST["CurrentPassword"]) ? $_REQUEST["CurrentPassword"] : '';
    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $vPassword = get_value('register_user', 'vPassword', 'iUserId', $user_id, '', 'true');
    } else {
        $tblname = "register_driver";
        $vPassword = get_value('register_driver', 'vPassword', 'iDriverId', $user_id, '', 'true');
    }

    # Check For Valid password #
    if ($CurrentPassword != "") {
        $hash = $vPassword;
        $checkValidPass = $generalobj->check_password($CurrentPassword, $hash);
        if ($checkValidPass == 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_WRONG_PASSWORD";
            echo json_encode($returnArr);exit;
        }
    }
    # Check For Valid password #

    //$updatedPassword = $generalobj->encrypt($Upass);
    $updatedPassword = $generalobj->encrypt_bycrypt($Upass);

    $Data_update_user['vPassword'] = $updatedPassword;

    if ($UserType == "Passenger") {

        $where = " iUserId = '$user_id'";
        $id = $obj->MySQLQueryPerform("register_user", $Data_update_user, 'update', $where);

        if ($id > 0) {

            $returnArr['Action'] = "1";
            $returnArr['message'] = getPassengerDetailInfo($user_id, "");
            echo json_encode($returnArr);

        } else {

            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            echo json_encode($returnArr);
        }

    } else {
        $where = " iDriverId = '$user_id'";
        $id = $obj->MySQLQueryPerform("register_driver", $Data_update_user, 'update', $where);

        if ($id > 0) {

            $returnArr['Action'] = "1";
            $returnArr['message'] = getDriverDetailInfo($user_id);
            echo json_encode($returnArr);

        } else {

            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            echo json_encode($returnArr);
        }
    }


?>