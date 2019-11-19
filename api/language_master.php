<?php 

    $sql = "SELECT * FROM  `language_master` WHERE eStatus = 'Active' ";
    $all_label = $obj->MySQLSelect($sql);
    $returnArr['language_master_code'] = $all_label;
    echo json_encode($returnArr);
    exit;

?>