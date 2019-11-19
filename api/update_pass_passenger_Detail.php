<?php 

    $user_id_auto = isset($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : '';
    $Upass = isset($_REQUEST["pass"]) ? $_REQUEST["pass"] : '';

    $Password_passenger = $generalobj->encrypt($Upass);
    $where = " iUserId = '$user_id_auto'";
    $Data_update_passenger['vPassword'] = $Password_passenger;

    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);

    if ($id > 0) {

        echo json_encode(getPassengerDetailInfo($user_id_auto, "none"));

    } else {

        echo "Failed.";
    }


?>