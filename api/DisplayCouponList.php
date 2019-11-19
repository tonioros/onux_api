<?php 

    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Passenger';
    if ($userType == "Passenger") {
        $tblname = "register_user";
        $fields = 'iUserId, vPhone,vPhoneCode as vPhoneCode, vEmail, vName, vLastName';
        $condfield = 'iUserId';
        $sqld = "SELECT ru.vCurrencyPassenger as vCurrency,cu.vSymbol FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iMemberId . "'";
    } else {
        $tblname = "register_driver";
        $fields = 'iDriverId, vPhone,vCode as vPhoneCode, vEmail, vName, vLastName';
        $condfield = 'iDriverId';
        $sqld = "SELECT rd.vCurrencyDriver as vCurrency,cu.vSymbol FROM register_driver as rd LEFT JOIN currency as cu ON rd.vCurrencyDriver = cu.vName WHERE iDriverId = '" . $iMemberId . "'";
    }
    $db_currency = $obj->MySQLSelect($sqld);
    $vCurrency = $db_currency[0]['vCurrency'];
    $vSymbol = $db_currency[0]['vSymbol'];
    if ($vCurrency == "" || $vCurrency == null) {
        $sql = "SELECT vName,vSymbol from currency WHERE eDefault = 'Yes'";
        $currencyData = $obj->MySQLSelect($sql);
        $vCurrency = $currencyData[0]['vName'];
        $vSymbol = $currencyData[0]['vSymbol'];
    }
    $sql = "SELECT * from coupon WHERE eStatus = 'Active'";
    $couponData = $obj->MySQLSelect($sql);
    if (count($couponData) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $couponData;
        $returnArr['vCurrency'] = $vCurrency;
        $returnArr['vSymbol'] = $vSymbol;
        $obj->MySQLClose();
        echo json_encode($returnArr);exit;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_RECORDS_FOUND1";
        $returnArr['vCurrency'] = $vCurrency;
        $returnArr['vSymbol'] = $vSymbol;
        $obj->MySQLClose();
        echo json_encode($returnArr);exit;
    }
?>