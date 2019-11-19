<?php 
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $vLangLabel = isset($_REQUEST['vLangLabel']) ? $_REQUEST['vLangLabel'] : '';
    $vLangLabel = urldecode(stripslashes($vLangLabel));
    //$vLangLabel = '{"LBL_NO_REFERRAL_CODES":"No Referral Code Found"}';
    $vCode = isset($_REQUEST['vCode']) ? clean($_REQUEST['vCode']) : '';
    $vLangLabelArr = json_decode($vLangLabel, true); //echo "<pre>";print_r($vLangLabelArr);exit;
    if (count($vLangLabelArr) > 0) {
        foreach ($vLangLabelArr as $key => $val) {
            $vLabel = $key;
            $vValue = $val;
            $sql = "SELECT LanguageLabelId FROM `language_label` where vLabel = '" . $vLabel . "' AND vCode = '" . $vCode . "'";
            $db_language_label = $obj->MySQLSelect($sql);
            $count = count($db_language_label);
            if ($count > 0) {
                $where = " LanguageLabelId = '" . $db_language_label[0]['LanguageLabelId'] . "'";
                $data_label_update['vValue'] = $vValue;
                $obj->MySQLQueryPerform("language_label", $data_label_update, 'update', $where);
                //UpdateOtherLanguage($vLabel,$vValue,$vCode,'language_label');
            } else {
                $sql = "SELECT LanguageLabelId FROM `language_label_other` where vLabel = '" . $vLabel . "' AND vCode = '" . $vCode . "'";
                $db_language_label_other = $obj->MySQLSelect($sql);
                $countOther = count($db_language_label_other);
                if ($countOther > 0) {
                    $where = " LanguageLabelId = '" . $db_language_label_other[0]['LanguageLabelId'] . "'";
                    $data_label_update_other['vValue'] = $vValue;
                    $obj->MySQLQueryPerform("language_label_other", $data_label_update_other, 'update', $where);
                    //UpdateOtherLanguage($vLabel,$vValue,$vCode,'language_label_other');
                }
            }
        }
        $returnArr['Action'] = "1";
        $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($vCode, "1");
        $returnArr['message'] = "LBL_UPDATE_MSG_TXT";
        echo json_encode($returnArr);
        exit;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        echo json_encode($returnArr);
        exit;
    }


?>