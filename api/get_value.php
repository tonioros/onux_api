<?php 

    global $obj;
    $returnArr = array();
    $table = isset($_REQUEST['table']) ? clean($_REQUEST['table']) : '';
    $field_name = isset($_REQUEST['field_name']) ? clean($_REQUEST['field_name']) : '';
    $condition_field = isset($_REQUEST['condition_field']) ? clean($_REQUEST['condition_field']) : '';
    $condition_value = isset($_REQUEST['condition_value']) ? clean($_REQUEST['condition_value']) : '';

    $where = ($condition_field != '') ? ' WHERE ' . $condition_field : '';
    $where .= ($where != '' && $condition_value != '') ? ' = "' . $condition_value . '"' : '';

    $returnArr = get_value($table, $field_name, $condition_field, $condition_value);

    echo json_encode($returnArr);
    exit;

?>