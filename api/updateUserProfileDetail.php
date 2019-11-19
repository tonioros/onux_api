<?php 

    $vName = isset($_REQUEST["vName"]) ? $_REQUEST["vName"] : '';
    $vLastName = isset($_REQUEST["vLastName"]) ? stripslashes($_REQUEST["vLastName"]) : '';
    $vPhone = isset($_REQUEST["vPhone"]) ? $_REQUEST["vPhone"] : '';
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST['iMemberId'] : '';
    $phoneCode = isset($_REQUEST["vPhoneCode"]) ? $_REQUEST['vPhoneCode'] : '';
    $vCountry = isset($_REQUEST["vCountry"]) ? $_REQUEST['vCountry'] : '';
    $currencyCode = isset($_REQUEST["CurrencyCode"]) ? $_REQUEST['CurrencyCode'] : '';
    $languageCode = isset($_REQUEST["LanguageCode"]) ? $_REQUEST['LanguageCode'] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST['UserType'] : 'Passenger';
    $vEmail = isset($_REQUEST["vEmail"]) ? $_REQUEST['vEmail'] : '';
    $tProfileDescription = isset($_REQUEST["tProfileDescription"]) ? $_REQUEST['tProfileDescription'] : '';
    $eSelectWorkLocation = isset($_REQUEST["eSelectWorkLocation"]) ? $_REQUEST['eSelectWorkLocation'] : 'Dynamic';
    $vInviteCode = isset($_REQUEST["vInviteCode"]) ? $_REQUEST['vInviteCode'] : '';

    if ($vInviteCode != "") {
        $check_inviteCode = $generalobj->validationrefercode($vInviteCode);
        if ($check_inviteCode == "" || $check_inviteCode == "0" || $check_inviteCode == 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_INVITE_CODE_INVALID";
            echo json_encode($returnArr);
            exit;
        } else {
            $inviteRes = explode("|", $check_inviteCode);
            $iRefUserId = $inviteRes[0];
            $eRefType = $inviteRes[1];
        }
    }

    $first = substr($vPhone, 0, 1);
    if ($first == "0") {
        $vPhone = substr($vPhone, 1);
    }

    if ($userType != "Driver") {
        $vEmail_userId_check = get_value('register_user', 'iUserId', 'vEmail', $vEmail, '', 'true');
        $vPhone_userId_check = get_value('register_user', 'iUserId', 'vPhone', $vPhone, '', 'true');

        $where = " iUserId = '$iMemberId'";
        $tableName = "register_user";

        $Data_update_User['vPhoneCode'] = $phoneCode;
        $Data_update_User['vCurrencyPassenger'] = $currencyCode;
        $currentLanguageCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');

        /*$vPhoneCode_orig =  get_value('register_user', 'vPhoneCode', 'iUserId',$iMemberId,'','true');
        $vPhone_orig =  get_value('register_user', 'vPhone', 'iUserId',$iMemberId,'','true');
        $vEmail_orig =  get_value('register_user', 'vEmail', 'iUserId',$iMemberId,'','true');*/
        $sqlp = "SELECT vPhoneCode,vPhone,vEmail,vInviteCode FROM register_user WHERE iUserId = '" . $iMemberId . "'";
        $passengerData = $obj->MySQLSelect($sqlp);
        $vPhoneCode_orig = $passengerData[0]['vPhoneCode'];
        $vPhone_orig = $passengerData[0]['vPhone'];
        $vEmail_orig = $passengerData[0]['vEmail'];
        $UservInviteCode = $passengerData[0]['vInviteCode'];
    } else {
        $vEmail_userId_check = get_value('register_driver', 'iDriverId', 'vEmail', $vEmail, '', 'true');
        $vPhone_userId_check = get_value('register_driver', 'iDriverId', 'vPhone', $vPhone, '', 'true');

        $where = " iDriverId = '$iMemberId'";
        $tableName = "register_driver";

        $Data_update_User['vCode'] = $phoneCode;
        $Data_update_User['vCurrencyDriver'] = $currencyCode;
        $Data_update_User['tProfileDescription'] = $tProfileDescription;
        //$Data_update_User['eSelectWorkLocation']=$eSelectWorkLocation;
        /*$currentLanguageCode =  get_value('register_driver', 'vLang', 'iDriverId',$iMemberId,'','true');

        $vPhoneCode_orig =  get_value('register_driver', 'vCode', 'iDriverId',$iMemberId,'','true');
        $vPhone_orig =  get_value('register_driver', 'vPhone', 'iDriverId',$iMemberId,'','true');
        $vEmail_orig =  get_value('register_driver', 'vEmail', 'iDriverId',$iMemberId,'','true');*/
        $sqlp = "SELECT vLang,vCode,vPhone,vEmail,vInviteCode FROM register_driver WHERE iDriverId = '" . $iMemberId . "'";
        $passengerData = $obj->MySQLSelect($sqlp);
        $currentLanguageCode = $passengerData[0]['vLang'];
        $vPhoneCode_orig = $passengerData[0]['vCode'];
        $vPhone_orig = $passengerData[0]['vPhone'];
        $vEmail_orig = $passengerData[0]['vEmail'];
        $UservInviteCode = $passengerData[0]['vInviteCode'];
    }

    // $currentLanguageCode = ($obj->MySQLSelect("SELECT vLang FROM ".$tableName." WHERE".$where)[0]['vLang']);

    if ($vEmail_userId_check != "" && $vEmail_userId_check != $iMemberId) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_ALREADY_REGISTERED_TXT";
        echo json_encode($returnArr);exit;
    }
    if ($vPhone_userId_check != "" && $vPhone_userId_check != $iMemberId) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_MOBILE_EXIST";
        echo json_encode($returnArr);exit;
    }

    if ($vPhone_orig != $vPhone || $vPhoneCode_orig != $phoneCode) {
        $Data_update_User['ePhoneVerified'] = "No";
    }
    if ($vEmail_orig != $vEmail) {
        $Data_update_User['eEmailVerified'] = "No";
    }

    $Data_update_User['vName'] = $vName;
    $Data_update_User['vLastName'] = $vLastName;
    $Data_update_User['vPhone'] = $vPhone;
    $Data_update_User['vCountry'] = $vCountry;
    $Data_update_User['vLang'] = $languageCode;
    if ($vEmail != "") {
        $Data_update_User['vEmail'] = $vEmail;
    }
    if ($UservInviteCode != "" && $vInviteCode != "") {
        $Data_update_User['iRefUserId'] = $iRefUserId;
        $Data_update_User['eRefType'] = $eRefType;
    }

    $id = $obj->MySQLQueryPerform($tableName, $Data_update_User, 'update', $where);

    if ($currentLanguageCode != $languageCode) {
        $returnArr['changeLangCode'] = "Yes";
        $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($languageCode, "1");
        $returnArr['vLanguageCode'] = $languageCode;
        /*$returnArr['langType'] = get_value('language_master', 'eDirectionCode', 'vCode',$languageCode,'','true');
        $returnArr['vGMapLangCode'] = get_value('language_master', 'vGMapLangCode', 'vCode',$languageCode,'','true');*/
        $sql_LangCode = "SELECT eDirectionCode,vGMapLangCode FROM language_master WHERE `vCode` = '" . $languageCode . "' ";
        $Data_checkLangCode = $obj->MySQLSelect($sql_LangCode);
        $returnArr['langType'] = $Data_checkLangCode[0]['eDirectionCode'];
        $returnArr['vGMapLangCode'] = $Data_checkLangCode[0]['vGMapLangCode'];
        $sql = "SELECT vCode, vGMapLangCode, eDirectionCode as eType, vTitle,vCurrencyCode,vCurrencySymbol,eDefault  FROM  `language_master` WHERE  `eStatus` = 'Active' ";
        $defLangValues = $obj->MySQLSelect($sql);
        $returnArr['LIST_LANGUAGES'] = $defLangValues;
        for ($i = 0; $i < count($defLangValues); $i++) {
            if ($defLangValues[$i]['eDefault'] == "Yes") {
                $returnArr['DefaultLanguageValues'] = $defLangValues[$i];
            }
        }
        $sql = "SELECT iCurrencyId,vName, vSymbol,iDispOrder, eDefault,Ratio,fThresholdAmount,eStatus  FROM  `currency` WHERE  `eStatus` = 'Active' ";
        $defCurrencyValues = $obj->MySQLSelect($sql);
        $returnArr['LIST_CURRENCY'] = $defCurrencyValues;
        for ($i = 0; $i < count($defCurrencyValues); $i++) {
            if ($defCurrencyValues[$i]['eDefault'] == "Yes") {
                $returnArr['DefaultCurrencyValues'] = $defCurrencyValues[$i];
            }
        }
    } else {
        $returnArr['changeLangCode'] = "No";
    }
    if ($userType != "Driver") {
        $returnArr['message'] = getPassengerDetailInfo($iMemberId, "");
    } else {
        $returnArr['message'] = getDriverDetailInfo($iMemberId);
    }
    if ($id > 0) {
        $returnArr['Action'] = "1";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    echo json_encode($returnArr);


?>