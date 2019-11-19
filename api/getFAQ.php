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

    $sql = "SELECT * FROM `faq_categories` WHERE eStatus='$status' AND vCode='" . $languageCode . "' ORDER BY iDisplayOrder ASC ";
    $Data = $obj->MySQLSelect($sql);

    $i = 0;
    if (count($Data) > 0) {
        $row = $Data;
        while (count($row) > $i) {
            $rows_questions = array();
            $iUniqueId = $row[$i]['iUniqueId'];

            $sql = "SELECT vTitle_" . $languageCode . " as vTitle,tAnswer_" . $languageCode . " as tAnswer FROM `faqs` WHERE iFaqcategoryId='" . $iUniqueId . "'";
            $row_questions = $obj->MySQLSelect($sql);

            $j = 0;
            while (count($row_questions) > $j) {
                $rows_questions[$j] = $row_questions[$j];
                $j++;
            }
            $row[$i]['Questions'] = $rows_questions;
            $i++;
        }

        $returnData['Action'] = "1";
        $returnData['message'] = $row;
    } else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_FAQ_NOT_AVAIL";
    }

    echo json_encode($returnData);

?>