<?php 

    $user_id_auto = isset($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : '';
    $Upass = isset($_REQUEST["pass"]) ? $_REQUEST["pass"] : '';

    $Password_driver = $generalobj->encrypt($Upass);

    $where = " iDriverId = '$user_id_auto'";
    $Data_update_driver['vPassword'] = $Password_driver;

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

    if ($id > 0) {
        echo json_encode(getDriverDetailInfo($user_id_auto));
    } else {
        echo "Failed.";
    }


?>