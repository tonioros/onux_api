<?php 
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    if ($UserType == "Passenger") {
        $tbl_name = "register_user";
        $currencycode = "vCurrencyPassenger";
        $iMemberId = "iUserId";
        $eUserType = "Rider";
    } else {
        $tbl_name = "register_driver";
        $currencycode = "vCurrencyDriver";
        $iMemberId = "iDriverId";
        $eUserType = "Driver";
    }
    $userCurrencyCode = get_value($tbl_name, $currencycode, $iMemberId, $iUserId, '', 'true');
    //$user_available_balance = $generalobj->get_user_available_balance($iUserId,$eUserType);
    $user_available_balance = $generalobj->get_user_available_balance_app_display($iUserId, $eUserType);
    $returnArr['Action'] = "1";
    //$returnArr["MemberBalance"] = strval($generalobj->userwalletcurrency(0,$user_available_balance,$userCurrencyCode));
    $returnArr["MemberBalance"] = strval($user_available_balance);
    $obj->MySQLClose();
    echo json_encode($returnArr);exit;

?>