<?php 
    global $generalobj, $tconfig;
    $iUserFavAddressId = isset($_REQUEST['iUserFavAddressId']) ? $_REQUEST['iUserFavAddressId'] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eUserType = isset($_REQUEST["eUserType"]) ? $_REQUEST["eUserType"] : 'Passenger';

    $sql = "DELETE FROM user_fave_address WHERE `iUserFavAddressId`='" . $iUserFavAddressId . "'";
    $id = $obj->MySQLSelect($sql);
    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message1'] = "LBL_USER_ADDRESS_DELETED_TXT";
        if ($eUserType == "Passenger") {
            $returnArr['message'] = getPassengerDetailInfo($iUserId, "");
        } else {
            $returnArr['message'] = getDriverDetailInfo($iUserId);
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);exit;

?>