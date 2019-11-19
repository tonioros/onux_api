<?php 
    global $generalobj;

    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $user_type = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Driver';
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : '';
    $APP_TYPE = $eType;
    $vCountry = '';
    if ($user_type == "Passenger") {
        $tblname = "register_user";
        $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
    } else {
        $tblname = "register_driver";
        $driveData = get_value('register_driver', 'vLang,vCountry', 'iDriverId', $iMemberId);
        $vLangCode = $driveData[0]['vLang'];
        $vCountry = $driveData[0]['vCountry'];
    }
    if ($vLangCode == "" || $vLangCode == null) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
    $lbl_all = $languageLabelsArr['LBL_ALL'];

    //$APP_TYPE = $generalobj->getConfigurations("configurations", "APP_TYPE");
    if ($APP_TYPE == "Delivery") {
        $ssql .= " AND eType = 'Deliver'";
    } else if ($APP_TYPE == "Ride-Delivery") {
        $ssql .= " AND ( eType = 'Deliver' OR eType = 'Ride')";
    } else {
        $ssql .= " AND eType = '" . $APP_TYPE . "'";
    }

    if ($vCountry != "") {
        $iCountryId = get_value('country', 'iCountryId', 'vCountryCode', $vCountry, '', 'true');
        //$ssql.= " AND (iCountryId = '".$iCountryId."' OR iCountryId = '-1' OR iCountryId = '0')";
        $sql = "SELECT * FROM location_master WHERE eStatus='Active' AND iCountryId = '" . $iCountryId . "' AND eFor = 'VehicleType'";
        $db_country = $obj->MySQLSelect($sql);
        $country_str = "-1";
        if (count($db_country) > 0) {
            for ($i = 0; $i < count($db_country); $i++) {
                $country_str .= "," . $db_country[$i]['iLocationId'];
            }
        }
        $ssql .= " AND iLocationid IN ($country_str) ";
    }

    $sql = "SELECT iVehicleTypeId,vVehicleType_" . $vLangCode . " as vVehicleType,iLocationid,iCountryId,iStateId,iCityId,eType FROM `vehicle_type` WHERE 1" . $ssql . " AND eStatus = 'Active'";
    $db_vehicletype = $obj->MySQLSelect($sql);
    if ($APP_TYPE == 'UberX') {
        $sql = "SELECT vCarType FROM `driver_vehicle` where iDriverId ='" . $iMemberId . "'";
        $db_vCarType = $obj->MySQLSelect($sql);

        /* if(count($db_vCarType) > 0){
        $vehicle_service_id= explode(",", $db_vCarType[0]['vCarType']);
        $data_service = array();
        for($i=0;$i<count($db_vehicletype); $i++){
        $data_service[$i]=$db_vehicletype[$i];
        if(in_array($data_service[$i]['iVehicleTypeId'],$vehicle_service_id)){
        $data_service[$i]['VehicleServiceStatus']= 'true';
        }else{
        $data_service[$i]['VehicleServiceStatus']= 'false';
        }
        }
        } */
        if (count($db_vehicletype) > 0 && count($db_vCarType) > 0) {
            $vehicle_service_id = explode(",", $db_vCarType[0]['vCarType']);
            for ($i = 0; $i < count($db_vehicletype); $i++) {
                if (in_array($db_vehicletype[$i]['iVehicleTypeId'], $vehicle_service_id)) {
                    $db_vehicletype[$i]['VehicleServiceStatus'] = 'true';
                } else {
                    $db_vehicletype[$i]['VehicleServiceStatus'] = 'false';
                }
            }
        }
    } else {

        if (count($db_vehicletype) > 0) {
            for ($i = 0; $i < count($db_vehicletype); $i++) {
                if ($db_vehicletype[$i]['iLocationid'] == "-1") {
                    $db_vehicletype[$i]['SubTitle'] = $lbl_all;
                } else {
                    $sql = "SELECT vLocationName FROM location_master WHERE iLocationId = '" . $db_vehicletype[$i]['iLocationid'] . "'";
                    $locationname = $obj->MySQLSelect($sql);
                    $db_vehicletype[$i]['SubTitle'] = $locationname[0]['vLocationName'];
                }

                /*$iCountryId= $db_vehicletype[$i]['iCountryId'];
                $iStateId= $db_vehicletype[$i]['iStateId'];
                $iCityId= $db_vehicletype[$i]['iCityId'];

                $subTitle = "";
                if($iCountryId == "" || $iCountryId == 0 || $iCountryId == "0" || $iCountryId == -1 || $iCountryId == "-1"){
                $subTitle = $lbl_all;
                }else{
                $country = get_value('country', 'vCountry', 'iCountryId', $iCountryId, '', 'true');
                $subTitle = $country;
                }
                if($iStateId == "" || $iStateId == 0 || $iStateId == "0" || $iStateId == -1 || $iStateId == "-1"){
                $subTitle = $subTitle . "/".$lbl_all;
                }else{
                $state = get_value('state', 'vState', 'iStateId', $iStateId, '', 'true');
                $subTitle = $subTitle . "/".$state;
                }
                if($iCityId == "" || $iCityId == 0 || $iCityId == "0" || $iCityId == -1 || $iCityId == "-1"){
                $subTitle = $subTitle."/".$lbl_all;
                }else{
                $city = get_value('city', 'vCity', 'iCityId', $iCityId, '', 'true');
                $subTitle = $subTitle . "/".$city;
                }   */
                /*added for rental*/
                if (ENABLE_RENTAL_OPTION == 'Yes') {
                    $checkrentalquery = "SELECT count(iRentalPackageId) as totalrental FROM  `rental_package` WHERE iVehicleTypeId = '" . $db_vehicletype[$i]['iVehicleTypeId'] . "'";
                    $rental_data = $obj->MySQLSelect($checkrentalquery);
                    if ($rental_data[0]['totalrental'] > 0) {
                        $db_vehicletype[$i]['eRental'] = 'Yes';
                    } else {
                        $db_vehicletype[$i]['eRental'] = 'No';
                    }
                } else {
                    $db_vehicletype[$i]['eRental'] = 'No';
                }
                /*end added for rental*/
            }
        }
    }
    //$make = get_value('make', '*', 'eStatus', 'Active');
    $sql1 = "select * from make where eStatus = 'Active' ORDER BY vMake ASC ";
    $make = $obj->MySQLSelect($sql1);
    $start = @date('Y');
    $end = '1970';
    $year = array();
    for ($j = $start; $j >= $end; $j--) {
        $year[] = strval($j);
        //$year .= $j.",";
    }

    //echo "<pre>";print_r($year);exit;
    $carlist = array();
    if (count($make) > 0) {
        //echo "<pre>";print_r($make);exit;
        for ($i = 0; $i < count($make); $i++) {
            //$ModelArr['List']=get_value('model', '*', 'iMakeId', $make[$i]['iMakeId']);
            $sql = "SELECT  * FROM  `model` WHERE iMakeId = '" . $make[$i]['iMakeId'] . "' AND `eStatus` = 'Active' ORDER BY vTitle ASC ";
            $db_model = $obj->MySQLSelect($sql);
            $ModelArr['List'] = $db_model;
            $carlist[$i]['iMakeId'] = $make[$i]['iMakeId'];
            $carlist[$i]['vMake'] = $make[$i]['vMake'];
            $carlist[$i]['vModellist'] = $ModelArr['List'];
        }
        $data['year'] = $year;
        $data['carlist'] = $carlist;

        $data['vehicletypelist'] = $db_vehicletype;

        if (count($db_vehicletype) == 0) {
            $returnArr['message1'] = "LBL_EDIT_VEHI_RESTRICTION_TXT";
        }

        $returnArr['Action'] = "1";
        $returnArr['message'] = $data;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    //echo "<pre>";print_r($data);exit;
    $obj->MySQLClose();
    echo json_encode($returnArr);exit;
?>