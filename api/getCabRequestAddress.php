<?php 
    global $generalobj, $obj;

    $iCabRequestId = isset($_REQUEST["iCabRequestId"]) ? $_REQUEST["iCabRequestId"] : '';
    $iDriverId = isset($_REQUEST["GeneralMemberId"]) ? $_REQUEST["GeneralMemberId"] : '';
    $fields = "iVehicleTypeId,eType,tSourceAddress,tDestAddress,tUserComment,iRentalPackageId";

    $Data_cab_request = get_value('cab_request_now', $fields, 'iCabRequestId', $iCabRequestId, '', '');
    $eType = $Data_cab_request[0]['eType'];
    // changed for rental
    if ($Data_cab_request[0]['iRentalPackageId'] == 0) {
        $Data_cab_request[0]['iRentalPackageId'] = "";
    }
    $iRentalPackageId = $Data_cab_request[0]['iRentalPackageId'];
    // end changed for rental
    //if($eType == "UberX"){
    $vLang = get_value('register_driver', 'vLang', 'iDriverId', $iDriverId, '', 'true');
    if ($vLang == "" || $vLang == null) {
        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    // changed for rental
    if ($iRentalPackageId != '') {
        $fields = "iRentalPackageId,fPrice,vPackageName_" . $vLang . "";
        $Data_Rental = get_value('rental_package', $fields, 'iRentalPackageId', $iRentalPackageId, '', '');
        //$fPrice = $Data_Rental[0]['fPrice'];
        $PackageName = $Data_Rental[0]['vPackageName_' . $vLang];
        //$Data_cab_request[0]['fPrice'] = $fPrice;
        $Data_cab_request[0]['PackageName'] = $PackageName;
    }
    // end changed for rental

    $iVehicleTypeId = $Data_cab_request[0]['iVehicleTypeId'];
    $sqlv = "SELECT iVehicleCategoryId,vVehicleType_" . $vLang . " as vVehicleTypeName from vehicle_type WHERE iVehicleTypeId = '" . $iVehicleTypeId . "'";
    $tripVehicleData = $obj->MySQLSelect($sqlv);
    $iVehicleCategoryId = $tripVehicleData[0]['iVehicleCategoryId'];
    $vVehicleTypeName = $tripVehicleData[0]['vVehicleTypeName'];
    if ($iVehicleCategoryId != 0) {
        $vVehicleCategoryName = get_value('vehicle_category', 'vCategory_' . $vLang, 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
        $vVehicleTypeName = $vVehicleCategoryName . "-" . $vVehicleTypeName;
    }
    if ($eType == "UberX") {
        $Data_cab_request[0]['SelectedTypeName'] = $vVehicleTypeName;
    }
    $Data_cab_request[0]['VehicleTypeName'] = $vVehicleTypeName;
    /*}else{
    $Data_cab_request[0]['SelectedTypeName']    = "";
    } */
    if (!empty($Data_cab_request)) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $Data_cab_request[0];
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);
    exit;
