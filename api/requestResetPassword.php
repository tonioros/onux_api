<?php 
    global $generalobj, $obj, $tconfig;
    $Emid = isset($_REQUEST["vEmail"]) ? $_REQUEST["vEmail"] : '';
    $userType = isset($_REQUEST["UserType"]) ? clean($_REQUEST["UserType"]) : ''; // UserType = Driver/Passenger
    if ($userType == "" || $userType == null) {
        $userType = "Passenger";
    }
    if ($userType == "Passenger") {
        $tblname = "register_user";
        $fields = 'iUserId as iMemberId, vPhone,vPhoneCode as vPhoneCode, vEmail, vName, vLastName, vPassword, vLang';
        $condfield = 'iUserId';
        $EncMembertype = base64_encode(base64_encode('rider'));
    } else {
        $tblname = "register_driver";
        $fields = 'iDriverId  as iMemberId, vPhone,vCode as vPhoneCode, vEmail, vName, vLastName,	vPassword, vLang';
        $condfield = 'iDriverId';
        $EncMembertype = base64_encode(base64_encode('driver'));
    }
    $sql = "select $fields from $tblname where vEmail = '" . $Emid . "'";
    $db_member = $obj->MySQLSelect($sql);
    if (count($db_member) > 0) {
        $vLangCode = $db_member[0]['vLang'];
        if ($vLangCode == "" || $vLangCode == null) {
            $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
        $clickherelabel = $languageLabelsArr['LBL_CLICKHERE_SIGNUP'];

        $milliseconds = time();
        $tempGenrateCode = substr($milliseconds, 1);
        $Today = Date('Y-m-d H:i:s');
        $today = base64_encode(base64_encode($Today));
        $type = $EncMembertype;
        $id = $generalobj->encrypt($db_member[0]["iMemberId"]);
        $newToken = $generalobj->RandomString(32);
        $url = $tconfig["tsite_url"] . 'reset_password.php?type=' . $type . '&id=' . $id . '&_token=' . $newToken;
        $activation_text = '<a href="' . $url . '" target="_blank"> ' . $clickherelabel . ' </a>';
        $maildata['EMAIL'] = $db_member[0]["vEmail"];
        $maildata['NAME'] = $db_member[0]["vName"] . " " . $db_member[0]["vLastName"];
        $maildata['LINK'] = $activation_text;
        $status = $generalobj->send_email_user("CUSTOMER_RESET_PASSWORD", $maildata);
        if ($status == 1) {
            $sql = "UPDATE $tblname set vPassword_token='" . $newToken . "' WHERE vEmail='" . $Emid . "' and eStatus != 'Deleted'";
            $obj->sql_query($sql);

            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_PASSWORD_SENT_TXT";
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_ERROR_PASSWORD_MAIL";
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_WRONG_EMAIL_PASSWORD_TXT";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);exit;

?>