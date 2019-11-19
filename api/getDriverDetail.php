<?php 

$Did = isset($_REQUEST["DriverAutoId"]) ? $_REQUEST["DriverAutoId"] : '';
$GCMID = isset($_REQUEST["GCMID"]) ? $_REQUEST["GCMID"] : '';

$sql = "SELECT iGcmRegId FROM `register_driver` WHERE iDriverId='$Did'";
$Data = $obj->MySQLSelect($sql);

if (count($Data) > 0) {

    $iGCMregID = $Data[0]['iGcmRegId'];

    if ($GCMID != '') {

        if ($iGCMregID != $GCMID) {
            $where = " iDriverId = '$Did' ";

            $Data_update_driver['iGcmRegId'] = $GCMID;

            $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
        }

    }

}
$obj->MySQLClose();
echo json_encode(getDriverDetailInfo($Did));

exit;

