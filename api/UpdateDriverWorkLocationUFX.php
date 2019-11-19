<?php 

$iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
$vWorkLocationLatitude = isset($_REQUEST["vWorkLocationLatitude"]) ? $_REQUEST["vWorkLocationLatitude"] : '';
$vWorkLocationLongitude = isset($_REQUEST["vWorkLocationLongitude"]) ? $_REQUEST["vWorkLocationLongitude"] : '';
$vWorkLocation = isset($_REQUEST["vWorkLocation"]) ? $_REQUEST["vWorkLocation"] : '';

$where = " iDriverId='$iDriverId'";
$Data_update_driver['vWorkLocationLatitude'] = $vWorkLocationLatitude;
$Data_update_driver['vWorkLocationLongitude'] = $vWorkLocationLongitude;
$Data_update_driver['vWorkLocation'] = $vWorkLocation;

$id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

if ($id) {
    $returnArr['Action'] = "1";
} else {
    $returnArr['Action'] = "0";
    $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
}
$obj->MySQLClose();
echo json_encode($returnArr);exit;
?>