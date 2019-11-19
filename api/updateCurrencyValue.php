<?php 

    $Uid = isset($_REQUEST["UserID"]) ? $_REQUEST["UserID"] : '';
    $currencyCode = isset($_REQUEST["vCurrencyCode"]) ? $_REQUEST["vCurrencyCode"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';

    if ($UserType == "Driver") {
        $where = " iDriverId = '$Uid'";
        $Data_update_user['vCurrencyDriver'] = $currencyCode;
        $id = $obj->MySQLQueryPerform("register_driver", $Data_update_user, 'update', $where);
    } else {
        $where = " iUserId = '$Uid'";
        $Data_update_user['vCurrencyPassenger'] = $currencyCode;
        $id = $obj->MySQLQueryPerform("register_user", $Data_update_user, 'update', $where);
    }

    if ($id) {
        echo "SUCCESS";
    } else {
        echo "UpdateFailed";
    }

?>