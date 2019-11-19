<?php 
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    //$APP_TYPE = $generalobj->getConfigurations("configurations", "APP_TYPE");

    if ($UserType == "Passenger") {
        // $tblname = "register_user";
        // $fields = 'iUserId as iMemberId, vPhone,vPhoneCode as vPhoneCode, vEmail, vName, vLastName,vPassword, vLang';
        $condfield = 'iUserId';
    } else {
        // $tblname = "register_driver";
        // $fields = 'iDriverId  as iMemberId, vPhone,vCode as vPhoneCode, vEmail, vName, vLastName,vPassword, vLang';
        $condfield = 'iDriverId';
    }

    if ($APP_TYPE == "UberX") {
        $sql = "SELECT iTripId FROM trips WHERE 1=1 AND $condfield = '" . $iMemberId . "' AND vTripPaymentMode != 'Cash' AND eType!='Ride' AND (iActive=	'Active' OR iActive='On Going Trip')";
        $checkStatus = $obj->MySQLSelect($sql);
    } else {
        $sql = "SELECT iTripId FROM trips WHERE 1=1 AND $condfield = '" . $iMemberId . "' AND vTripPaymentMode != 'Cash' AND eType='Ride' AND (iActive=	'Active' OR iActive='On Going Trip') order by iTripId DESC limit 1";
        $checkStatus = $obj->MySQLSelect($sql);
    }

    if (count($checkStatus) > 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = 'LBL_DIS_ALLOW_EDIT_CARD';
    } else {
        $returnArr['Action'] = "1";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);
    exit;

?>