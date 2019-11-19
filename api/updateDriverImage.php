<?php 

    $user_id_auto = isset($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : '';
    $UIpath = isset($_REQUEST["Path"]) ? $_REQUEST["Path"] : '';

    $where = " iDriverId = '$user_id_auto'";
    $Data_update_driver['vImage'] = $UIpath;

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

    if ($id) {
        echo "Update Successful..";
    } else {

        echo "Failed.";
    }


?>