<?php 

    global $generalobj;

    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iUserId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
    $tripTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $ListType = isset($_REQUEST["ListType"]) ? $_REQUEST["ListType"] : 'All';
    if ($page == "0" || $page == 0) {
        $page = 1;
    }
    if ($UserType == "Passenger") {
        $UserType = "Rider";
    }

    $ssql = '';
    if ($ListType != "All") {
        $ssql .= " AND eType ='" . $ListType . "'";
    }
    $per_page = 10;
    $sql_all = "SELECT COUNT(iUserWalletId) As TotalIds FROM user_wallet WHERE  iUserId='" . $iUserId . "' AND eUserType = '" . $UserType . "' " . $ssql . " ";
    $data_count_all = $obj->MySQLSelect($sql_all);
    $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);

    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;

    //$user_available_balance = $generalobj->get_user_available_balance($iUserId,$UserType);

    //$sql = "SELECT tripRate.vRating1 as TripRating,tr.* FROM `trips` as tr,`ratings_user_driver` as tripRate  WHERE  tr.iUserId='$iUserId' AND tripRate.iTripId=tr.iTripId AND tripRate.eUserType='$UserType' AND (tr.iActive='Canceled' || tr.iActive='Finished') ORDER BY tr.iTripId DESC" . $limit;
    $sql = "SELECT * from user_wallet where iUserId='" . $iUserId . "' AND eUserType = '" . $UserType . "' " . $ssql . " ORDER BY iUserWalletId DESC" . $limit;
    $Data = $obj->MySQLSelect($sql);
    $totalNum = count($Data);

    $vSymbol = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
    if ($UserType == 'Driver') {
        /*$uservSymbol = get_value('register_driver', 'vCurrencyDriver', 'iDriverId',$iUserId,'','true');
        $vLangCode = get_value('register_driver', 'vLang', 'iDriverId',$iUserId,'','true');*/
        $UserData = get_value('register_driver', 'vCurrencyDriver,vLang', 'iDriverId', $iUserId);
        $uservSymbol = $UserData[0]['vCurrencyDriver'];
        $vLangCode = $UserData[0]['vLang'];
    } else {
        /*$uservSymbol = get_value('register_user', 'vCurrencyPassenger', 'iUserId',$iUserId,'','true');
        $vLangCode = get_value('register_user', 'vLang', 'iUserId',$iUserId,'','true');  */
        $UserData = get_value('register_user', 'vCurrencyPassenger,vLang', 'iUserId', $iUserId);
        $uservSymbol = $UserData[0]['vCurrencyPassenger'];
        $vLangCode = $UserData[0]['vLang'];
    }

    $userCurrencySymbol = get_value('currency', 'vSymbol', 'vName', $uservSymbol, '', 'true');

    if ($vLangCode == "" || $vLangCode == null) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");

    $i = 0;
    if (count($Data) > 0) {

        $row = $Data;
        $prevbalance = 0;
        while (count($row) > $i) {

            if (!empty($row[$i]['tDescription'])) {
                $pat = '/\#([^\"]*?)\#/';
                preg_match($pat, $row[$i]['tDescription'], $tDescription_value);
                $tDescription_translate = $languageLabelsArr[$tDescription_value[1]];
                $row[$i]['tDescription'] = str_replace($tDescription_value[0], $tDescription_translate, $row[$i]['tDescription']);
            }

            // Convert Into Timezone
            if ($tripTimeZone != "") {
                $serverTimeZone = date_default_timezone_get();
                $row[$i]['dDate'] = converToTz($row[$i]['dDate'], $tripTimeZone, $serverTimeZone);
            }
            // Convert Into Timezone
            if ($row[$i]['eType'] == "Credit") {
                $row[$i]['currentbal'] = $prevbalance + $row[$i]['iBalance'];
            } else {
                $row[$i]['currentbal'] = $prevbalance - $row[$i]['iBalance'];
            }
            $prevbalance = $row[$i]['currentbal'];
            $row[$i]['dDateOrig'] = $row[$i]['dDate'];
            $row[$i]['dDate'] = date('d-M-Y', strtotime($row[$i]['dDate']));

            //$row[$i]['currentbal'] = $vSymbol.$row[$i]['currentbal'];
            //$row[$i]['iBalance'] = $vSymbol.$row[$i]['iBalance'];
            $row[$i]['currentbal'] = $generalobj->userwalletcurrency($row[$i]['fRatio_' . $uservSymbol], $row[$i]['currentbal'], $uservSymbol);
            $row[$i]['iBalance'] = $generalobj->userwalletcurrency($row[$i]['fRatio_' . $uservSymbol], $row[$i]['iBalance'], $uservSymbol);
            $i++;
        }

        //$returnData['message'] = array_reverse($row);
        $returnData['message'] = $row;
        if ($TotalPages > $page) {
            $returnData['NextPage'] = $page + 1;
        } else {
            $returnData['NextPage'] = 0;
        }

        /*
        $returnData['user_available_balance_default']=$vSymbol.$user_available_balance;
        $returnData['user_available_balance'] = strval($generalobj->userwalletcurrency(0,$user_available_balance,$uservSymbol));*/
        $user_available_balance = $generalobj->get_user_available_balance_app_display($iUserId, $UserType);
        $returnData['user_available_balance_default'] = $user_available_balance;
        $returnData['user_available_balance'] = strval($user_available_balance);
        $returnData['Action'] = "1";
        #echo "<pre>"; print_r($returnData); exit;
        echo json_encode($returnData);

    } else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_NO_TRANSACTION_AVAIL";
        $returnData['user_available_balance'] = $userCurrencySymbol . "0.00";
        echo json_encode($returnData);
    }


?>