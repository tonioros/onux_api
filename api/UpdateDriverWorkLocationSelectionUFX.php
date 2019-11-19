<?php
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $eSelectWorkLocation = isset($_REQUEST["eSelectWorkLocation"]) ? $_REQUEST['eSelectWorkLocation'] : 'Dynamic';
    $vWorkLocation = isset($_REQUEST["vWorkLocation"]) ? $_REQUEST['vWorkLocation'] : '';
    $vWorkLocationLatitude = isset($_REQUEST["vWorkLocationLatitude"]) ? $_REQUEST['vWorkLocationLatitude'] : '';
    $vWorkLocationLongitude = isset($_REQUEST["vWorkLocationLongitude"]) ? $_REQUEST['vWorkLocationLongitude'] : '';

    $where = " iDriverId = '$iDriverId'";
    $tableName = "register_driver";

    $Data_update_driver['eSelectWorkLocation'] = $eSelectWorkLocation;
    if ($vWorkLocation != "" && $vWorkLocationLatitude != "" && $vWorkLocationLongitude != "") {
        $Data_update_driver['vWorkLocation'] = $vWorkLocation;
        $Data_update_driver['vWorkLocationLatitude'] = $vWorkLocationLatitude;
        $Data_update_driver['vWorkLocationLongitude'] = $vWorkLocationLongitude;
    }
    $id = $obj->MySQLQueryPerform($tableName, $Data_update_driver, 'update', $where);
    if ($id > 0) {
        $returnArr['Action'] = "1";

        $returnArr['message'] = getDriverDetailInfo($iDriverId);
        $returnArr['message1'] = "LBL_WORKLOCATION_UPDATE_TXT";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);exit;

?>