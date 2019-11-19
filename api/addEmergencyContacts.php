<?php 
    global $generalobj;

    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '0';
    $Phone = isset($_REQUEST["Phone"]) ? $_REQUEST["Phone"] : '0';
    $vName = isset($_REQUEST["vName"]) ? $_REQUEST["vName"] : '0';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';

    $sql = "SELECT vPhone FROM user_emergency_contact WHERE iUserId = '" . $iUserId . "' AND vPhone='" . $Phone . "' AND eUserType='" . $UserType . "'";

    $Data_Exist = $obj->MySQLSelect($sql);

    if (count($Data_Exist) > 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_EME_CONTACT_EXIST";
    } else {
        $Data['vName'] = $vName;
        $Data['vPhone'] = $Phone;
        $Data['iUserId'] = $iUserId;
        $Data['eUserType'] = $UserType;

        $id = $obj->MySQLQueryPerform("user_emergency_contact", $Data, 'insert');

        if ($id > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_EME_CONTACT_LIST_UPDATE";
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }
    }

    echo json_encode($returnArr);

?>