<?php 

    $Did = isset($_REQUEST["DriverAutoId"]) ? $_REQUEST["DriverAutoId"] : '';
    $availabilityStatus = isset($_REQUEST["Status"]) ? $_REQUEST["Status"] : '';

    $where = " iDriverId='$Did'";

    $Data_update_driver['tLastOnline'] = @date("Y-m-d H:i:s");
    $Data_update_driver['vAvailability'] = $availabilityStatus;

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

    if ($id > 0) {
        echo "UpdateSuccessful";
    } else {
        echo "Failed";
    }

?>