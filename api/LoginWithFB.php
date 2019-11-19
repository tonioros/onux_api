<?php 

    $fbid = isset($_REQUEST["iFBId"]) ? $_REQUEST["iFBId"] : '';
    $Fname = isset($_REQUEST["vFirstName"]) ? $_REQUEST["vFirstName"] : '';
    $Lname = isset($_REQUEST["vLastName"]) ? $_REQUEST["vLastName"] : '';
    $email = isset($_REQUEST["vEmail"]) ? $_REQUEST["vEmail"] : '';
    $GCMID = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $vDeviceType = isset($_REQUEST["vDeviceType"]) ? $_REQUEST["vDeviceType"] : 'Android';
    $eLoginType = isset($_REQUEST["eLoginType"]) ? $_REQUEST["eLoginType"] : 'Facebook';
    $user_type = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $vCurrency = isset($_REQUEST["vCurrency"]) ? $_REQUEST["vCurrency"] : '';
    $vLang = isset($_REQUEST["vLang"]) ? $_REQUEST["vLang"] : '';
    $vFirebaseDeviceToken = isset($_REQUEST["vFirebaseDeviceToken"]) ? $_REQUEST["vFirebaseDeviceToken"] : '';
    $vImageURL = isset($_REQUEST["vImageURL"]) ? $_REQUEST["vImageURL"] : '';

    if ($fbid == "") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";

        echo json_encode($returnArr);exit;
    }
    //$DeviceType = "Android";
    $DeviceType = $vDeviceType;

    if ($user_type == "Passenger") {
        $tblname = "register_user";
        $iMemberId = 'iUserId';
        $vCurrencyMember = "vCurrencyPassenger";
        $vImageFiled = 'vImgName';
    } else {
        $tblname = "register_driver";
        $iMemberId = 'iDriverId';
        $vCurrencyMember = "vCurrencyDriver";
        $vImageFiled = 'vImage';
    }

    if ($user_type == "Passenger") {
        $sql = "SELECT iUserId as iUserId,eStatus,vFbId,vLang,vTripStatus,eSignUpType,vImgName as vImage  FROM $tblname WHERE 1=1 AND IF('$email'!='',vEmail = '$email',0) OR IF('$fbid'!='',vFbId = '$fbid',0)";
    } else {
        $sql = "SELECT iDriverId as iUserId,eStatus,vFbId,vLang,vTripStatus,eSignUpType,vImage as vImage FROM $tblname WHERE 1=1 AND IF('$email'!='',vEmail = '$email',0) OR IF('$fbid'!='',vFbId = '$fbid',0)";
    }

    /*if($email != ''){
    $sql = "SELECT iUserId,eStatus,vFbId,vLang,vTripStatus FROM `register_user` WHERE vEmail='$email' OR vFbId='$fbid'";
    }else{
    $sql = "SELECT iUserId,eStatus,vFbId,vLang,vTripStatus FROM `register_user` WHERE vFbId='$fbid'";
    }   */
    $Data = $obj->MySQLSelect($sql);
    if ($user_type == "Passenger") {
        /*$iCabRequestId= get_value('cab_request_now', 'max(iCabRequestId)', 'iUserId',$Data[0]['iUserId'],'','true');
        $eStatus_cab= get_value('cab_request_now', 'eStatus', 'iCabRequestId',$iCabRequestId,'','true');*/
        $sql_cabrequest = "SELECT iCabRequestId,eStatus FROM `cab_request_now` WHERE iUserId='" . $Data[0]['iUserId'] . "' ORDER BY iCabRequestId DESC LIMIT 0,1";
        $Data_cabrequest = $obj->MySQLSelect($sql_cabrequest);
        $iCabRequestId = $Data_cabrequest[0]['iCabRequestId'];
        $eStatus_cab = $Data_cabrequest[0]['eStatus'];
    }
    if (count($Data) > 0) {
        if ($Data[0]['eStatus'] == "Active" || ($user_type == "Driver" && $Data[0]['eStatus'] != "Deleted")) {

            $iUserId_passenger = $Data[0]['iUserId'];
            //$where = " iUserId = '$iUserId_passenger' ";
            $where = " $iMemberId = '$iUserId_passenger' ";
            if ($Data[0]['vLang'] == "" && $vLang == "") {
                $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                $Data_update_passenger['vLang'] = $vLang;
            }
            if ($vLang != "") {
                $Data_update_passenger['vLang'] = $vLang;
                $Data[0]['vLang'] = $vLang;
            }
            if ($vCurrency != "") {
                $Data_update_passenger[$vCurrencyMember] = $vCurrency;
            }

            ## Upload Image of Member if SignUp from Google, Facebook Or Twitter ##
            $vImage = $Data[0]['vImage'];
            if ($fbid != 0 || $fbid != "") {
                $userid = $Data[0]['iUserId'];
                $eSignUpType = $eLoginType;
                $UserImage = UploadUserImage($userid, $user_type, $eSignUpType, $fbid, $vImageURL);
                if ($UserImage != "") {
                    $where = " $iMemberId = '$userid' ";
                    $Data_update_image_member[$vImageFiled] = $UserImage;
                    $imageuploadid = $obj->MySQLQueryPerform($tblname, $Data_update_image_member, 'update', $where);
                }
            }
            ## Upload Image of Member if SignUp from Google, Facebook Or Twitter ##

            if ($GCMID != '') {

                $Data_update_passenger['iGcmRegId'] = $GCMID;
                $Data_update_passenger['eDeviceType'] = $DeviceType;
                $Data_update_passenger['vFbId'] = $fbid;
                $Data_update_passenger['eSignUpType'] = $eLoginType;
                $Data_update_passenger['tSessionId'] = session_id() . time();
                $Data_update_passenger['vFirebaseDeviceToken'] = $vFirebaseDeviceToken;
                /*if($Data[0]['vFbId'] =='' || $Data[0]['vFbId'] == "0"){
                $Data_update_passenger['vFbId']=$fbid;
                } */

                $id = $obj->MySQLQueryPerform($tblname, $Data_update_passenger, 'update', $where);
            }

            if ($user_type == "Passenger") {
                if ($eStatus_cab == "Requesting") {
                    $where1 = " iCabRequestId = '$iCabRequestId' ";
                    $Data_update_cab_now['eStatus'] = "Cancelled";

                    $id = $obj->MySQLQueryPerform("cab_request_now", $Data_update_cab_now, 'update', $where1);
                }
            }

            $returnArr['changeLangCode'] = "Yes";
            $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($Data[0]['vLang'], "1");
            $returnArr['vLanguageCode'] = $Data[0]['vLang'];

            $sql_LangCode = "SELECT eDirectionCode,vGMapLangCode FROM language_master WHERE `vCode` = '" . $Data[0]['vLang'] . "' ";
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

            $returnArr['Action'] = "1";
            if ($user_type == "Passenger") {
                $returnArr['message'] = getPassengerDetailInfo($Data[0]['iUserId'], '');
                createUserLog("Passenger", "No", $Data[0]['iUserId'], "Android");
            } else {
                $returnArr['message'] = getDriverDetailInfo($Data[0]['iUserId'], '');
                createUserLog("Driver", "No", $Data[0]['iUserId'], "Android");
            }

            echo json_encode($returnArr);exit;

        } else {
            $returnArr['Action'] = "0";
            /*if($Data[0]['eStatus'] !="Deleted"){
            $returnArr['message'] ="LBL_CONTACT_US_STATUS_NOTACTIVE_PASSENGER";
            }else{
            $returnArr['message'] ="LBL_ACC_DELETE_TXT";
            }*/
            if ($Data[0]['eStatus'] == "Deleted") {
                $returnArr['message'] = "LBL_ACC_DELETE_TXT";
            }
            echo json_encode($returnArr);exit;
        }

    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "DO_REGISTER";
        echo json_encode($returnArr);exit;
    }
