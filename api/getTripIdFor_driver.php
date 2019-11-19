<?php 


    $driver_id = isset($_REQUEST["driver_id"]) ? $_REQUEST["driver_id"] : '';

    $sql = "SELECT iTripId FROM `register_driver` WHERE iDriverId = '$driver_id'";
    $Data = $obj->MySQLSelect($sql);

    if (count($Data) == 1) {
        $current_trip_id = $Data[0]['iTripId'];
    }
    echo $current_trip_id;


?>