<?php 
    $Did = isset($_REQUEST["DriverAutoId"]) ? $_REQUEST["DriverAutoId"] : '';

    $sql = "SELECT make.vMake, model.vTitle, dv.*  FROM `driver_vehicle` dv, make, model WHERE dv.iDriverId='$Did' AND dv.`iMakeId` = make.`iMakeId` AND dv.`iModelId` = model.`iModelId` AND dv.`eStatus`='Active'";

    $Data = $obj->MySQLSelect($sql);
    if (count($Data) > 0) {

        $i = 0;
        while (count($Data) > $i) {

            $Data[$i]['vModel'] = $Data[$i]['vTitle'];
            $i++;
        }

        $returnArr['carList'] = $Data;

        echo json_encode($returnArr);
    } else {
        $returnArr['action'] = 0; //duplicate entry
        $returnArr['message'] = 'Fail';

        echo json_encode($returnArr);
    }


?>