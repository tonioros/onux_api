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
    $sql = "SELECT * FROM `help_detail_categories` WHERE eStatus='$status' AND vCode='" . $languageCode . "' ORDER BY iDisplayOrder ASC ";
    $Data = $obj->MySQLSelect($sql);
    if (count($Data) > 0) {
        $arr_cat = array();
        for ($i = 0; $i < count($Data); $i++) {
            $arr_cat[$i]['iHelpDetailCategoryId'] = $Data[$i]['iHelpDetailCategoryId'];
            $arr_cat[$i]['vTitle'] = $Data[$i]['vTitle'];
            $arr_cat[$i]['vTitle'] = $Data[$i]['vTitle'];
            $arr_cat[$i]['iUniqueId'] = $Data[$i]['iUniqueId'];
        }
        $returnData['Action'] = "1";
        $returnData['message'] = $arr_cat;
    } else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_HELP_DETAIL_NOT_AVAIL";
    }
    echo json_encode($returnData);

?>