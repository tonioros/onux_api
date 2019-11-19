<?php 

    global $generalobj, $obj;

    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';

    $Data_logout = array();

    if ($userType == "Passenger") {
        $Data_logout['eLogout'] = 'Yes';
        $tableName = "register_user";
        $where = " iUserId='" . $iMemberId . "'";
        $id = $obj->MySQLQueryPerform($tableName, $Data_logout, 'update', $where);
    } else {
        $Data_logout['vAvailability'] = 'Not Available';
        $Data_logout['eLogout'] = 'Yes';
        $tableName = "register_driver";
        $where = " iDriverId='" . $iMemberId . "'";
        $id = $obj->MySQLQueryPerform($tableName, $Data_logout, 'update', $where);

        $curr_date = date('Y-m-d H:i:s');
        $selct_query = "select * from driver_log_report WHERE iDriverId = '" . $iMemberId . "' AND dLogoutDateTime = '0000-00-00 00:00:00' order by `iDriverLogId` desc limit 0,1";
        $get_data_log = $obj->sql_query($selct_query);

        if (count($get_data_log) > 0) {
            $update_sql = "UPDATE driver_log_report set dLogoutDateTime = '" . $curr_date . "' WHERE iDriverLogId ='" . $get_data_log[0]['iDriverLogId'] . "'";
            $result = $obj->sql_query($update_sql);
        }
    }

    if ($id) {
        $returnArr['Action'] = "1";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);
    exit;

?>