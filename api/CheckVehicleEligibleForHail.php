<?php 
    global $generalobj, $tconfig;
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';

    if ($COMMISION_DEDUCT_ENABLE == 'Yes' && ($APP_PAYMENT_MODE == "Cash" || $APP_PAYMENT_MODE == "Cash-Card")) {
        $user_available_balance = $generalobj->get_user_available_balance($iDriverId, "Driver");
        $driverDetail = get_value('register_driver AS rd LEFT JOIN currency AS c ON c.vName=rd.vCurrencyDriver', 'rd.vCurrencyDriver,c.Ratio,c.vSymbol', 'rd.iDriverId', $iDriverId);
        $ratio = $driverDetail[0]['Ratio'];
        $currencySymbol = $driverDetail[0]['vSymbol'];

        $vLang = get_value('register_driver', 'vLang', 'iDriverId', $iDriverId, '', 'true');
        if ($vLang == "" || $vLang == null) {
            $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        $languageLabelsArr = getLanguageLabelsArr($vLang, "1");

        if ($WALLET_MIN_BALANCE > $user_available_balance) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "REQUIRED_MINIMUM_BALNCE";
            if ($APP_TYPE == "UberX") {
                $returnArr['Msg'] = str_replace('####', $currencySymbol . ($WALLET_MIN_BALANCE * $ratio), $languageLabelsArr['LBL_REQUIRED_MINIMUM_BALNCE_UBERX']);
            } else {
                $returnArr['Msg'] = str_replace('####', $currencySymbol . ($WALLET_MIN_BALANCE * $ratio), $languageLabelsArr['LBL_REQUIRED_MINIMUM_BALNCE_HAIL']);
            }
            echo json_encode($returnArr);exit;
        }
    }

    $iDriverVehicleId = get_value('register_driver', 'iDriverVehicleId', 'iDriverId', $iDriverId, '', 'true');
    if ($iDriverVehicleId > 0) {
        $sql = "SELECT vCarType FROM driver_vehicle WHERE iDriverVehicleId = '" . $iDriverVehicleId . "'";
        $vCarType = $obj->MySQLSelect($sql);
        $vehicleIds = explode(",", $vCarType[0]['vCarType']);
        $vehicleListIds = implode("','", $vehicleIds);
        $sql1 = "SELECT count(iVehicleTypeId) as total_ridevehicle FROM vehicle_type WHERE iVehicleTypeId IN ('" . $vehicleListIds . "') AND eType = 'Ride'";
        $Vehiclelist = $obj->MySQLSelect($sql1);
        if ($Vehiclelist[0]['total_ridevehicle'] > 0) {
            $returnArr['Action'] = "1";
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_VEHICLE_ELIGIBLE_FOR_HAIL_RIDE_MSG";
        }
    } /*else {
    $query="SELECT vCarType FROM driver_vehicle WHERE iDriverId = '".$iDriverId."'";
    $vCarType = $obj->MySQLSelect($query);
    foreach ($vCarType as $key => $value) {
    $vehicleType = $value['vCarType'];
    $vehicle_ids = explode(",", $vehicleType);
    $vehicle_id_list = implode("','", $vehicle_ids);
    $query1 = "SELECT count(iVehicleTypeId) as total_ridevehicle FROM vehicle_type WHERE iVehicleTypeId IN ('".$vehicle_id_list."') AND eType = 'Ride'";
    $Vehiclelist = $obj->MySQLSelect($query1);
    if($Vehiclelist[0]['total_ridevehicle'] > 0){
    $returnArr['Action']="1";
    } else {
    $returnArr['Action']="0";
    $returnArr['message']="Your Have Not Any Eligible Vehicle For Hali Ride.Please Add Vehicle.";
    }
    }
    }*/

    $obj->MySQLClose();
    echo json_encode($returnArr);exit;

?>