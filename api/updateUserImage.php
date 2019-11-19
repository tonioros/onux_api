<?php 

    $user_id_auto = isset($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : '';
    $UIpath = isset($_REQUEST["Path"]) ? $_REQUEST["Path"] : '';

    $where = " iUserId = '$user_id_auto'";
    $Data_update_passenger['vImgName'] = $UIpath;

    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);

    if ($id) {
        echo "Update Successful..";
    } else {

        echo "Failed.";
    }


?>