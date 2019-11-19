<?php 

    $Fname = isset($_REQUEST["Fname"]) ? $_REQUEST["Fname"] : '';
    $Lname = isset($_REQUEST["Lname"]) ? $_REQUEST["Lname"] : '';
    $Umobile = isset($_REQUEST["mobile"]) ? $_REQUEST["mobile"] : '';
    $user_id_auto = isset($_REQUEST["user_id"]) ? $_REQUEST['user_id'] : '';
    $phoneCode = isset($_REQUEST["phoneCode"]) ? $_REQUEST['phoneCode'] : '';

    $where = " iUserId = '$user_id_auto'";
    $Data_update_passenger['vName'] = $Fname;
    $Data_update_passenger['vLastName'] = $Lname;
    $Data_update_passenger['vPhone'] = $Umobile;
    $Data_update_passenger['vPhoneCode'] = $phoneCode;

    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);

    if ($id > 0) {
        echo json_encode(getPassengerDetailInfo($user_id_auto, "none"));
    } else {
        echo "Failed.";
    }


?>