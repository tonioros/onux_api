<?php 
    $Uid = isset($_REQUEST["access_sign_token_user_id_auto"]) ? $_REQUEST["access_sign_token_user_id_auto"] : '';

    $where = " iUserId='$Uid'";
    $Data_update_passenger['vLogoutDev'] = "false";

    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);

    if ($id) {
        echo "Update Successful";
    }


?>