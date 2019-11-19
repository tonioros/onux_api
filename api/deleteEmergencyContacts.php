<?php 
    global $generalobj;

    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '0';
    $iEmergencyId = isset($_REQUEST["iEmergencyId"]) ? $_REQUEST["iEmergencyId"] : '0';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';

    $sql = "DELETE FROM user_emergency_contact WHERE `iEmergencyId`='" . $iEmergencyId . "' AND `iUserId`='" . $iUserId . "' AND eUserType = '" . $UserType . "'";
    $id = $obj->sql_query($sql);
    // echo "ID:".$id;exit;
    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_EME_CONTACT_LIST_UPDATE";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    echo json_encode($returnArr);

?>