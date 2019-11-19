<?php 
    global $generalobj, $tconfig;
    $iUserId = isset($_REQUEST['iUserId']) ? clean($_REQUEST['iUserId']) : '';
    $eUserType = isset($_REQUEST['eUserType']) ? clean($_REQUEST['eUserType']) : 'Passenger';
    if ($eUserType == "Passenger") {
        $eUserType = "Rider";
    }
    $sql = "select * from `user_address` where iUserId = '" . $iUserId . "' AND eUserType = '" . $eUserType . "' AND eStatus = 'Active' ORDER BY iUserAddressId DESC";
    $db_userdata = $obj->MySQLSelect($sql);
    if (count($db_userdata) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $db_userdata;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_USER_ADDRESS_FOUND";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);
    exit;

?>