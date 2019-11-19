<?php
    $status = "Active";
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $appType = isset($_REQUEST['appType']) ? clean($_REQUEST['appType']) : '';
    $languageCode = "";
    if ($appType == "Driver") {
        $languageCode = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');
    } else {
        $languageCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
    }
    if ($languageCode == "") {
        $languageCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $sql = "SELECT vTitle_" . $languageCode . " as vTitle,iHelpDetailId FROM `help_detail` WHERE eStatus='$status'";
    $Data_detail = $obj->MySQLSelect($sql);
    if (count($Data_detail) > 0) {
        $returnData['Action'] = "1";
        $returnData['message'] = $Data_detail;
    } else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_HELP_DETAIL_NOT_AVAIL";
    }
    echo json_encode($returnData);

?>
