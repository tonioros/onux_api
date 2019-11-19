<?php 
    $sql = "SELECT * FROM vehicle_type";

    $row_result_vehivle_type = $obj->MySQLSelect($sql);

    $arr_temp['Types'] = $row_result_vehivle_type;
    echo json_encode($arr_temp);

?>