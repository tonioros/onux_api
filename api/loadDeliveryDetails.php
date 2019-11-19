<?php 
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    // packagename changes
    $vLang = get_value("register_driver", "vLang", "iDriverId", $iDriverId, '', 'true');
    if ($vLang == "" || $vLang == null) {
        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $sql = "SELECT tr.vReceiverName,tr.vReceiverMobile,tr.tPickUpIns,tr.tDeliveryIns,tr.tPackageDetails,pt.vName_" . $vLang . " as packageType,concat(ru.vName,' ',ru.vLastName) as senderName, concat('+',ru.vPhoneCode,'',ru.vPhone) as senderMobile from trips as tr, register_user as ru, package_type as pt WHERE ru.iUserId = tr.iUserId AND tr.iTripId = '" . $iTripId . "' AND pt.iPackageTypeId = tr.iPackageTypeId";
    $Data = $obj->MySQLSelect($sql);

    if (count($Data) > 0 && $iTripId != "") {
        if ($CALLMASKING_ENABLED == "Yes") {
            $Data[0]['senderMobile'] = substr($Data[0]['senderMobile'], 0, -5) . 'XXXXX';
        }
        $returnArr['Action'] = "1";
        $returnArr['message'] = $Data[0];
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    echo json_encode($returnArr);

?>