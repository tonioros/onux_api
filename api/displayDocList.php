<?php 

    global $generalobj, $tconfig;

    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $memberType = isset($_REQUEST['MemberType']) ? clean($_REQUEST['MemberType']) : 'Driver';
    $iDriverVehicleId = isset($_REQUEST['iDriverVehicleId']) ? clean($_REQUEST['iDriverVehicleId']) : '';
    $doc_usertype = isset($_REQUEST['doc_usertype']) ? clean(strtolower($_REQUEST['doc_usertype'])) : 'driver';
    $eType = isset($_REQUEST['eType']) ? clean($_REQUEST['eType']) : ''; //  Ride, Delivery OR UberX only for APP_TYPE Ride-Delivery-UberX , and it is blank for another APP_TYPE

    $ssql = "";
    if ($eType != "") {
        $ssql .= " AND dm.eType = '" . $eType . "'";
    } else {
        if ($APP_TYPE == "Delivery") {
            $ssql .= " AND dm.eType = 'Delivery'";
        } else if ($APP_TYPE == "Ride-Delivery") {
            $ssql .= " AND ( dm.eType = 'Deliver' OR dm.eType = 'Ride')";
        } else if ($APP_TYPE == "Ride-Delivery-UberX") {
            $ssql .= " AND ( dm.eType = 'Deliver' OR dm.eType = 'Ride' OR dm.eType = 'UberX')";
        } else {
            $ssql .= " AND dm.eType = '" . $APP_TYPE . "'";
        }

    }

    if ($doc_usertype == "vehicle") {
        $doc_usertype = "car";
    }
    $doc_userid = ($doc_usertype == 'car') ? $iDriverVehicleId : $iMemberId;
    //$APP_TYPE = $generalobj->getConfigurations("configurations", "APP_TYPE");

    /*$vCountry = get_value('register_driver', 'vCountry', 'iDriverId', $iMemberId,'',true);
    $vLang = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId,'',true);*/
    $UserData = get_value('register_driver', 'vCountry,vLang', 'iDriverId', $iMemberId);
    $vCountry = $UserData[0]['vCountry'];
    $vLang = $UserData[0]['vLang'];
    if ($vLang == '' || $vLang == null) {
        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $sql1 = "SELECT dm.doc_masterid masterid, dm.doc_usertype , dm.eType,dm.doc_name_" . $vLang . " as doc_name ,dm.ex_status,dm.status, COALESCE(dl.doc_id,  '' ) as doc_id,COALESCE(dl.doc_masterid, '') as masterid_list ,COALESCE(dl.ex_date, '') as ex_date,COALESCE(dl.doc_file, '') as doc_file, COALESCE(dl.status, '') as status FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" . $doc_userid . "' ) dl on dl.doc_masterid=dm.doc_masterid
		where dm.doc_usertype='" . $doc_usertype . "' AND (dm.country='" . $vCountry . "' OR dm.country='All') and dm.status='Active' $ssql ";
    $db_vehicle = $obj->MySQLSelect($sql1);
    if (count($db_vehicle) > 0) {
        //$Photo_Gallery_folder = $tconfig['tsite_upload_driver_doc']."/".$iMemberId."/";
        if ($doc_usertype == "driver") {
            $Photo_Gallery_folder = $tconfig['tsite_upload_driver_doc'] . "/" . $iMemberId . "/";
        } else {
            $Photo_Gallery_folder = $tconfig['tsite_upload_vehicle_doc_panel'] . "/" . $iDriverVehicleId . "/";
        }
        for ($i = 0; $i < count($db_vehicle); $i++) {
            if ($db_vehicle[$i]['doc_file'] != "") {
                $db_vehicle[$i]['vimage'] = $Photo_Gallery_folder . $db_vehicle[$i]['doc_file'];
            } else {
                $db_vehicle[$i]['vimage'] = "";
            }

            ## Checking for expire date of document ##
            $ex_date = $db_vehicle[$i]['ex_date'];
            $todaydate = date('Y-m-d');
            if ($ex_date == "" || $ex_date == "0000-00-00") {
                $expire_document = "No";
            } else {
                if (strtotime($ex_date) < strtotime($todaydate)) {
                    $expire_document = "Yes";
                } else {
                    $expire_document = "No";
                }
            }
            $db_vehicle[$i]['EXPIRE_DOCUMENT'] = $expire_document;
            ## Checking for expire date of document ##
        }
        $returnArr['Action'] = "1";
        $returnArr['message'] = $db_vehicle;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DOC_AVAIL";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);
    exit;

?>