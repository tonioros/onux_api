<?php 
    // $returnArr['List']=($obj->MySQLSelect("SELECT * FROM currency WHERE eStatus='Active'"));
    $returnArr['List'] = get_value('currency', '*', 'eStatus', 'Active');
    echo json_encode($returnArr);

?>