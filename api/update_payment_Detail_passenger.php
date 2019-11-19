<?php 


    $user_id_auto = isset($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : '';
    $UcrdNO = isset($_REQUEST["crd_no"]) ? $_REQUEST["crd_no"] : '';
    $UexMonth = isset($_REQUEST["expMonth"]) ? $_REQUEST["expMonth"] : '';
    $UexYear = isset($_REQUEST["expYear"]) ? $_REQUEST["expYear"] : '';
    $UCVV = isset($_REQUEST["cvv_no"]) ? $_REQUEST['cvv_no'] : '';

    $where = " iUserId = '$user_id_auto'";
    $Data_update_passenger['vCreditCard'] = $UcrdNO;
    $Data_update_passenger['vExpMonth'] = $UexMonth;
    $Data_update_passenger['vExpYear'] = $UexYear;
    $Data_update_passenger['vCvv'] = $UCVV;

    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);

    if ($id) {
        echo "Update Successful..";
    } else {

        echo "No Update.";
    }

?>