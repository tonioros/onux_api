<?php 
    global $generalobj, $tconfig;
    $iUserFavAddressId = isset($_REQUEST['iUserFavAddressId']) ? $_REQUEST['iUserFavAddressId'] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eUserType = isset($_REQUEST["eUserType"]) ? $_REQUEST["eUserType"] : 'Passenger'; // Passenger , Driver
    $vAddress = isset($_REQUEST["vAddress"]) ? $_REQUEST["vAddress"] : '';
    $vLatitude = isset($_REQUEST["vLatitude"]) ? $_REQUEST["vLatitude"] : '';
    $vLongitude = isset($_REQUEST["vLongitude"]) ? $_REQUEST["vLongitude"] : '';
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : 'Home'; // Home,Work
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $eStatus = isset($_REQUEST["eStatus"]) ? $_REQUEST["eStatus"] : 'Active';
    $dAddedDate = @date("Y-m-d H:i:s");
    $action = ($iUserFavAddressId != '') ? 'Edit' : 'Add';
    $Data_User_Address['iUserId'] = $iUserId;
    $Data_User_Address['eUserType'] = $eUserType;
    $Data_User_Address['vAddress'] = $vAddress;
    $Data_User_Address['vLatitude'] = $vLatitude;
    $Data_User_Address['vLongitude'] = $vLongitude;
    $Data_User_Address['eType'] = $eType;
    $Data_User_Address['dAddedDate'] = $dAddedDate;
    $Data_User_Address['vTimeZone'] = $vTimeZone;
    $Data_User_Address['eStatus'] = $eStatus;
    if ($action == "Add") {
        $insertid = $obj->MySQLQueryPerform("user_fave_address", $Data_User_Address, 'insert');
        $AddressId = $insertid;
    } else {
        $where = " iUserFavAddressId = '" . $iUserFavAddressId . "'";
        $insertid = $obj->MySQLQueryPerform("user_fave_address", $Data_User_Address, 'update', $where);
        $AddressId = $iUserAddressId;
    }

    if ($insertid > 0) {
        $returnArr['Action'] = "1";
        $returnArr['AddressId'] = $insertid;
        $returnArr['message1'] = "LBL_ADDRSS_ADD_SUCCESS";
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
    echo json_encode($returnArr);
    exit;

?>