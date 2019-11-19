<?php 
    // packagename changes
    $GeneralMemberId = isset($_REQUEST['GeneralMemberId']) ? trim($_REQUEST['GeneralMemberId']) : '';
    $GeneralUserType = isset($_REQUEST['GeneralUserType']) ? trim($_REQUEST['GeneralUserType']) : 'Passenger';
    if ($GeneralUserType == "Passenger") {
        $vLang = get_value("register_user", "vLang", "iUserId", $GeneralMemberId, '', 'true');
    } else {
        $vLang = get_value("register_driver", "vLang", "iDriverId", $GeneralMemberId, '', 'true');
    }
    if ($vLang == "" || $vLang == null) {
        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $vehicleTypes = get_value('package_type', 'iPackageTypeId,eStatus,vName_' . $vLang . ' as vName', 'eStatus', 'Active');

    if (count($vehicleTypes) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $vehicleTypes;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    echo json_encode($returnArr);

?>