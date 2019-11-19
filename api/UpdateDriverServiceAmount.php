<?php 
    $iVehicleTypeId = isset($_REQUEST['iVehicleTypeId']) ? $_REQUEST['iVehicleTypeId'] : '';
    $iDriverVehicleId = isset($_REQUEST['iDriverVehicleId']) ? $_REQUEST['iDriverVehicleId'] : '';
    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Driver';
    $fAmount = isset($_REQUEST['fAmount']) ? $_REQUEST['fAmount'] : '';
    if ($iDriverVehicleId == "" || $iDriverVehicleId == 0 || $iDriverVehicleId == null) {
        $iDriverVehicleId = get_value('register_driver', 'iDriverVehicleId', 'iDriverId', $iDriverId, '', 'true');
    }

    if ($APP_TYPE == "Ride-Delivery-UberX") {
        $query = "SELECT iDriverVehicleId FROM `driver_vehicle` WHERE iDriverId = '" . $iDriverId . "' AND eType = 'UberX'";
        $result = $obj->MySQLSelect($query);
        $iDriverVehicleId = $result[0]['iDriverVehicleId'];
    }

    $vCurrencyDriver = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $iDriverId, '', 'true');
    if ($vCurrencyDriver == "" || $vCurrencyDriver == null) {
        $vCurrencyDriver = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    }
    $vCurrencyData = get_value('currency', 'vSymbol, Ratio', 'vName', $vCurrencyDriver);
    $vCurrencyRatio = $vCurrencyData[0]['Ratio'];
    $Amount = $fAmount / $vCurrencyRatio;
    $Amount = round($Amount, 2);
    $sqlServicePro = "SELECT * FROM `service_pro_amount` WHERE iDriverVehicleId='" . $iDriverVehicleId . "' AND iVehicleTypeId='" . $iVehicleTypeId . "'";
    $serviceProData = $obj->MySQLSelect($sqlServicePro);
    if (count($serviceProData) > 0) {
        $updateQuery = "UPDATE service_pro_amount set fAmount='" . $Amount . "' WHERE iDriverVehicleId='" . $iDriverVehicleId . "' AND iVehicleTypeId='" . $iVehicleTypeId . "'";
        $id = $obj->sql_query($updateQuery);
    } else {
        $Data["iDriverVehicleId"] = $iDriverVehicleId;
        $Data["iVehicleTypeId"] = $iVehicleTypeId;
        $Data["fAmount"] = $Amount;
        $id = $obj->MySQLQueryPerform("service_pro_amount", $Data, 'insert');
    }
    if ($id) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_SERVICE_AMOUT_UPDATED";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);exit;

?>