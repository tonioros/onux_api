<?php 
    $mobileNo = isset($_REQUEST['MobileNo']) ? clean($_REQUEST['MobileNo']) : '';
    $mobileNo = str_replace('+', '', $mobileNo);
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Passenger';
    $REQ_TYPE = isset($_REQUEST["REQ_TYPE"]) ? $_REQUEST['REQ_TYPE'] : '';

    //$isdCode= $generalobj->getConfigurations("configurations","SITE_ISD_CODE");
    $isdCode = $SITE_ISD_CODE;
    //$toMobileNum= "+".$mobileNo;
    if ($userType == "Passenger") {
        $tblname = "register_user";
        $fields = 'iUserId, vPhone,vPhoneCode as vPhoneCode, vEmail, vName, vLastName';
        $condfield = 'iUserId';
        $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
    } else {
        $tblname = "register_driver";
        $fields = 'iDriverId, vPhone,vCode as vPhoneCode, vEmail, vName, vLastName';
        $condfield = 'iDriverId';
        $vLangCode = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');
    }

    if ($vLangCode == "" || $vLangCode == null) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");

    $str = "select * from send_message_templates where vEmail_Code='VERIFICATION_CODE_MESSAGE'";
    $res = $obj->MySQLSelect($str);
    $prefix = $res[0]['vBody_' . $vLangCode];

    //$prefix = $languageLabelsArr['LBL_VERIFICATION_CODE_TXT'];
    $verificationCode_sms = mt_rand(1000, 9999);
    $verificationCode_email = mt_rand(1000, 9999);
    $message = $prefix . ' ' . $verificationCode_sms;

    if ($iMemberId == "" && $REQ_TYPE == "DO_PHONE_VERIFY") {
        $toMobileNum = "+" . $mobileNo;
    } else {
        $sql = "select $fields from $tblname where $condfield = '" . $iMemberId . "'";
        $db_member = $obj->MySQLSelect($sql);

        $Data_Mail['vEmail'] = isset($db_member[0]['vEmail']) ? $db_member[0]['vEmail'] : '';
        $vFirstName = isset($db_member[0]['vName']) ? $db_member[0]['vName'] : '';
        $vLastName = isset($db_member[0]['vLastName']) ? $db_member[0]['vLastName'] : '';
        $Data_Mail['vName'] = $vFirstName . " " . $vLastName;
        $Data_Mail['CODE'] = $verificationCode_email;
        $mobileNo = $db_member[0]['vPhoneCode'] . $db_member[0]['vPhone'];
        $toMobileNum = "+" . $mobileNo;
    }

    $emailmessage = "";
    $phonemessage = "";
    if ($REQ_TYPE == "DO_EMAIL_PHONE_VERIFY") {
        $sendemail = $generalobj->send_email_user("APP_EMAIL_VERIFICATION_USER", $Data_Mail);
        if ($sendemail != true || $sendemail != "true" || $sendemail != "1") {
            $sendemail = 0;
        }
        $result = sendEmeSms($toMobileNum, $message);
        if ($result == 0) {
            $toMobileNum = "+" . $isdCode . $mobileNo;
            $result = sendEmeSms($toMobileNum, $message);
        }

        $returnArr['Action'] = "1";
        if ($sendemail == 0 && $result == 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_ACC_VERIFICATION_FAILED";
        } else {
            $returnArr['message_sms'] = $result == 0 ? "LBL_MOBILE_VERIFICATION_FAILED_TXT" : $verificationCode_sms;
            $returnArr['message_email'] = $sendemail == 0 ? "LBL_EMAIL_VERIFICATION_FAILED_TXT" : $verificationCode_email;
        }
        echo json_encode($returnArr);exit;
    } else if ($REQ_TYPE == "DO_PHONE_VERIFY") {
        $result = sendEmeSms($toMobileNum, $message);
        if ($result == 0) {
            $toMobileNum = "+" . $isdCode . $mobileNo;
            $result = sendEmeSms($toMobileNum, $message);
        }

        if ($result == 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_MOBILE_VERIFICATION_FAILED_TXT";
            echo json_encode($returnArr);exit;
        } else {
            $returnArr['Action'] = "1";
            $returnArr['message'] = $verificationCode_sms;
            echo json_encode($returnArr);exit;
        }
    } else if ($REQ_TYPE == "DO_EMAIL_VERIFY") {
        $sendemail = $generalobj->send_email_user("APP_EMAIL_VERIFICATION_USER", $Data_Mail);
        if ($sendemail != true || $sendemail != "true" || $sendemail != "1") {
            $sendemail = 0;
        }
        if ($sendemail == 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_EMAIL_VERIFICATION_FAILED_TXT";
            echo json_encode($returnArr);exit;
        } else {
            $returnArr['Action'] = "1";
            $returnArr['message'] = $Data_Mail['CODE'];
            echo json_encode($returnArr);exit;
        }
    } else if ($REQ_TYPE == "EMAIL_VERIFIED") {
        $where = " " . $condfield . " = '" . $iMemberId . "'";
        $Data['eEmailVerified'] = "Yes";
        $id = $obj->MySQLQueryPerform($tblname, $Data, 'update', $where);

        if ($id) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_EMAIl_VERIFIED";

            if ($userType == 'Passenger') {
                $returnArr['userDetails']['Action'] = "1";
                $returnArr['userDetails']['message'] = getPassengerDetailInfo($iMemberId);
            } else {
                $returnArr['userDetails']['Action'] = "1";
                $returnArr['userDetails']['message'] = getDriverDetailInfo($iMemberId);
            }
            echo json_encode($returnArr);exit;
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_EMAIl_VERIFIED_ERROR";
            echo json_encode($returnArr);exit;
        }

    } else if ($REQ_TYPE == "PHONE_VERIFIED") {

        $where = " " . $condfield . " = '" . $iMemberId . "'";
        $Data['ePhoneVerified'] = "Yes";
        $id = $obj->MySQLQueryPerform($tblname, $Data, 'update', $where);

        if ($id) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_PHONE_VERIFIED";
            if ($userType == 'Passenger') {
                $returnArr['userDetails']['Action'] = "1";
                $returnArr['userDetails']['message'] = getPassengerDetailInfo($iMemberId);
            } else {
                $returnArr['userDetails']['Action'] = "1";
                $returnArr['userDetails']['message'] = getDriverDetailInfo($iMemberId);
            }
            echo json_encode($returnArr);exit;
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_PHONE_VERIFIED_ERROR";
            echo json_encode($returnArr);exit;
        }
    }

    //    $returnArr['message'] =$verificationCode;
    //echo json_encode($returnArr);

?>