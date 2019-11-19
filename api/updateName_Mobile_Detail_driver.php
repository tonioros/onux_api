<?php 


    $Fname = isset($_REQUEST["Fname"]) ? $_REQUEST["Fname"] : '';
    $Lname = isset($_REQUEST["Lname"]) ? $_REQUEST["Lname"] : '';
    $Umobile = isset($_REQUEST["mobile"]) ? $_REQUEST["mobile"] : '';
    $user_id_auto = isset($_REQUEST["user_id"]) ? $_REQUEST['user_id'] : '';
    $phoneCode = isset($_REQUEST["phoneCode"]) ? $_REQUEST['phoneCode'] : '';

    $where = " iDriverId = '$user_id_auto'";
    $Data_update_driver['vName'] = $Fname;
    $Data_update_driver['vLastName'] = $Lname;
    $Data_update_driver['vPhone'] = $Umobile;
    $Data_update_driver['vCode'] = $phoneCode;

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

    if ($id > 0) {
        echo json_encode(getDriverDetailInfo($user_id_auto));
    } else {
        echo "Failed.";
    }

?>