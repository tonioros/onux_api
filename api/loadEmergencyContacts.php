<?php
    global $generalobj;

    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '0';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
    $GeneralUserType = isset($_REQUEST["GeneralUserType"]) ? $_REQUEST["GeneralUserType"] : 'Passenger';

    if ($UserType == "") {
        $UserType = $GeneralUserType;
    }
    //$data = get_value('user_emergency_contact', '*', 'iUserId', $iUserId);
    //$data = get_value('user_emergency_contact', '*', 'eUserType', $UserType,'','true');
    $sql = "SELECT * FROM user_emergency_contact WHERE iUserId='" . $iUserId . "' AND eUserType = '" . $UserType . "'";
    $data = $obj->MySQLSelect($sql);

    if (count($data) > 0) {
        $returnData['Action'] = "1";
        $returnData['message'] = $data;
    } else {
        $returnData['Action'] = "0";
    }
    echo json_encode($returnData);

?>