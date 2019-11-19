<?php
    $iDriverVehicleId = isset($_REQUEST['iDriverVehicleId']) ? $_REQUEST['iDriverVehicleId'] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $iMakeId = isset($_REQUEST["iMakeId"]) ? $_REQUEST["iMakeId"] : '';
    $iModelId = isset($_REQUEST["iModelId"]) ? $_REQUEST["iModelId"] : '';
    $iYear = isset($_REQUEST["iYear"]) ? $_REQUEST["iYear"] : '';
    $vLicencePlate = isset($_REQUEST["vLicencePlate"]) ? $_REQUEST["vLicencePlate"] : '';
    $eCarX = isset($_REQUEST["eCarX"]) ? $_REQUEST["eCarX"] : '';
    $eCarGo = isset($_REQUEST["eCarGo"]) ? $_REQUEST["eCarGo"] : '';
    $vColour = isset($_REQUEST["vColor"]) ? $_REQUEST["vColor"] : '';
    //$eStatus = ($generalobj->getConfigurations("configurations", "VEHICLE_AUTO_ACTIVATION") == 'Yes') ? 'Active' : 'Inactive';
    $vCarType = isset($_REQUEST["vCarType"]) ? $_REQUEST["vCarType"] : '';
    /*added for rental*/
    $vRentalCarType = isset($_REQUEST["vRentalCarType"]) ? $_REQUEST["vRentalCarType"] : '';
    $handiCap = isset($_REQUEST["HandiCap"]) ? $_REQUEST["HandiCap"] : 'No';
    $iVehicleCategoryId = isset($_REQUEST["iVehicleCategoryId"]) ? $_REQUEST["iVehicleCategoryId"] : '';
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : 'Ride'; //'Ride', 'Delivery', 'UberX'

    //$APP_TYPE = $generalobj->getConfigurations("configurations", "APP_TYPE");

    if (($APP_TYPE == "UberX") && ($iDriverVehicleId == "" || $iDriverVehicleId == 0 || $iDriverVehicleId == null)) {
        //    $iDriverVehicleId=get_value('register_driver', 'iDriverVehicleId', 'iDriverId', $iDriverId,'','true');
    }
    if ($eType == "UberX") {
        ## Check message if driver is online ##
        $sql = "select vAvailability from `register_driver` where iDriverId = '" . $iDriverId . "'";
        $db_available = $obj->MySQLSelect($sql);
        $vAvailability = $db_available[0]['vAvailability'];
        if ($vAvailability == "Available") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_CHANGE_SERVICE_AFTER_OFFLINE_TXT";
            echo json_encode($returnArr);
            exit;
        }
        ## Check message if driver is online ##

        $query = "SELECT iDriverVehicleId FROM `driver_vehicle` WHERE iDriverId = '" . $iDriverId . "' AND eType = 'UberX'";
        $result = $obj->MySQLSelect($query);
        if (count($result) > 0) {
            $iDriverVehicleId = $result[0]['iDriverVehicleId'];
        } else {
            $iDriverVehicleId = 0;
        }
    }
    /*if($APP_TYPE == "Ride-Delivery-UberX"){
    $query ="SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type`";
    $result = $obj->MySQLSelect($query);
    $vCarType = $result[0]['countId'];
    }  */

    $action = ($iDriverVehicleId != 0) ? 'Edit' : 'Add';
    if ($action == "Add") {
        $eStatus = "Inactive";
    }
    if ($action == "Edit" && $ENABLE_EDIT_DRIVER_VEHICLE == "No" && $eType != "UberX") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_EDIT_VEHICLE_DISABLED";
        echo json_encode($returnArr);
        exit;
    }
    $sql = "select iCompanyId from `register_driver` where iDriverId = '" . $iDriverId . "'";
    $db_usr = $obj->MySQLSelect($sql);
    $iCompanyId = $db_usr[0]['iCompanyId'];

    $Data_Driver_Vehicle['iDriverId'] = $iDriverId;
    $Data_Driver_Vehicle['iCompanyId'] = $iCompanyId;

    if (SITE_TYPE == "Demo") {
        $Data_Driver_Vehicle['eStatus'] = "Active";
    } else {
        if ($action == "Add") {
            $Data_Driver_Vehicle['eStatus'] = $eStatus;
        }
    }
    ## Update Vehicle Type For UberX ##
    if (($APP_TYPE == "UberX" || $eType == "UberX") && $action == "Edit") {
        $sql = "select vCarType from `driver_vehicle` where iDriverVehicleId = '" . $iDriverVehicleId . "'";
        $vCarTypeData = $obj->MySQLSelect($sql);
        $vCarTypeData = explode(",", $vCarTypeData[0]['vCarType']);
        $sql = "select iVehicleTypeId from `vehicle_type` where iVehicleCategoryId = '" . $iVehicleCategoryId . "'";
        $db_vehicategoryid = $obj->MySQLSelect($sql);
        $array_vehiclie_id = array();
        for ($i = 0; $i < count($db_vehicategoryid); $i++) {
            array_push($array_vehiclie_id, $db_vehicategoryid[$i]['iVehicleTypeId']);
        }
        $arraydiff = array_diff($vCarTypeData, $array_vehiclie_id);
        $sssql2 = "";
        if (count($arraydiff) > 0) {
            $sssql2 = implode(",", $arraydiff);
        }
        if ($vCarType != "") {
            $vCarType = $vCarType . "," . $sssql2;
            if ($sssql2 == "") {
                $vCarType = substr($vCarType, 0, -1);
            }
        } else {
            $vCarType = $sssql2;
        }
    }

    ## Update Vehicle Type For UberX ##
    $Data_Driver_Vehicle['eCarX'] = $eCarX;
    $Data_Driver_Vehicle['eCarGo'] = $eCarGo;
    $Data_Driver_Vehicle['vCarType'] = $vCarType;
    /*added for rental*/
    $Data_Driver_Vehicle['vRentalCarType'] = $vRentalCarType;
    $Data_Driver_Vehicle['eHandiCapAccessibility'] = $handiCap;
    $Data_Driver_Vehicle['eType'] = $eType;

    if ($iMakeId != "") {
        $Data_Driver_Vehicle['iMakeId'] = $iMakeId;
    }
    if ($iModelId != "") {
        $Data_Driver_Vehicle['iModelId'] = $iModelId;
    }

    if ($iYear != "") {
        $Data_Driver_Vehicle['iYear'] = $iYear;
    }

    /*if($vColour != ""){
    $Data_Driver_Vehicle['vColour'] = $vColour;
    }*/
    $Data_Driver_Vehicle['vColour'] = $vColour;
    if ($vLicencePlate != "") {
        $Data_Driver_Vehicle['vLicencePlate'] = $vLicencePlate;
    }

    if ($APP_TYPE == 'UberX' || $eType == 'UberX') {
        $Data_Driver_Vehicle['iCompanyId'] = "1";
        $Data_Driver_Vehicle['iMakeId'] = "3";
        $Data_Driver_Vehicle['iModelId'] = "1";
        $Data_Driver_Vehicle['iYear'] = Date('Y');
        $Data_Driver_Vehicle['vLicencePlate'] = "My Services";
        $Data_Driver_Vehicle['eStatus'] = "Active";
        $Data_Driver_Vehicle['eCarX'] = "Yes";
        $Data_Driver_Vehicle['eCarGo'] = "Yes";
    }

    // $Data_Driver_Vehicle['vColour'] = $vColour;
    // $Data_Driver_Vehicle['vLicencePlate'] = $vLicencePlate;

    if ($action == "Add") {

        $id = $obj->MySQLQueryPerform("driver_vehicle", $Data_Driver_Vehicle, 'insert');
    } else {
        $where = " iDriverVehicleId = '" . $iDriverVehicleId . "'";
        $id = $obj->MySQLQueryPerform("driver_vehicle", $Data_Driver_Vehicle, 'update', $where);
    }

    if ($id > 0) {
        $returnArr['Action'] = "1";
        //$returnArr['message'] = GetDriverDetail($iDriverId);
        if ($eType == "UberX") {
            $returnArr['message'] = ($action == 'Add') ? 'LBL_SERVICE_ADD_SUCCESS_NOTE' : 'LBL_SERVICE_UPDATE_SUCCESS';
        } else {
            $returnArr['message'] = ($action == 'Add') ? 'LBL_VEHICLE_ADD_SUCCESS_NOTE' : 'LBL_VEHICLE_UPDATE_SUCCESS';
        }
        $returnArr['VehicleInsertId'] = $id;
        $returnArr['VehicleStatus'] = $Data_Driver_Vehicle['eStatus'];
        //$eStatus = ($generalobj->getConfigurations("configurations", "VEHICLE_AUTO_ACTIVATION") == 'Yes') ? 'Active' : 'Inactive';
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);
    exit;

?>