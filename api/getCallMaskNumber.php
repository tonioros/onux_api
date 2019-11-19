<?php 
    global $generalobj, $tconfig;
    $returnArr = array();
    $iTripId = isset($_REQUEST['iTripid']) ? $_REQUEST['iTripid'] : '';
    $GeneralDeviceType = isset($_REQUEST['GeneralDeviceType']) ? $_REQUEST['GeneralDeviceType'] : '';
    $UserType = isset($_REQUEST['UserType']) ? $_REQUEST['UserType'] : '';

    $iDriverId = get_value('trips', 'iDriverId', 'iTripId', $iTripId, '', 'true');
    $vCountry = get_value('register_driver', 'vCountry', 'iDriverId', $iDriverId, '', true);
    if ($vCountry == "" || $vCountry == null) {
        $vCountry = $DEFAULT_COUNTRY_CODE_WEB;
    }

    $sql = "SELECT rd.vCode as DriverPhoneCode, rd.vPhone as DriverPhone, ru.vPhoneCode as UserPhoneCode, ru.vPhone as RiderPhone FROM `trips` as t LEFT JOIN `register_user` as ru on ru.iUserId = t.iUserId LEFT JOIN `register_driver` as rd on rd.iDriverId= t.iDriverId  WHERE t.iTripId = " . $iTripId . " AND (t.iActive != 'Canceled' && t.iActive != 'Finished')";
    $getTripDetails = $obj->MySQLSelect($sql);

    if (count($getTripDetails) > 0) {
        if ($UserType == "Driver") {
            $phonNum = '+' . $getTripDetails[0]['UserPhoneCode'] . $getTripDetails[0]['RiderPhone'];
        } else {
            $phonNum = '+' . $getTripDetails[0]['DriverPhoneCode'] . $getTripDetails[0]['DriverPhone'];
        }
        $returnArr['Action'] = "1";
        $returnArr['message'] = $phonNum;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    $obj->MySQLClose();
    echo json_encode($returnArr);
    exit;

?>