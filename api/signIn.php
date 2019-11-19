<?php

    $Emid = isset($_REQUEST["vEmail"]) ? $_REQUEST["vEmail"] : '';
    $Emid = strtolower($Emid);
    $Password_user = isset($_REQUEST["vPassword"]) ? $_REQUEST["vPassword"] : '';
    $GCMID = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $DeviceType = isset($_REQUEST["vDeviceType"]) ? $_REQUEST["vDeviceType"] : 'Android';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $vCurrency = isset($_REQUEST["vCurrency"]) ? $_REQUEST["vCurrency"] : '';
    $vLang = isset($_REQUEST["vLang"]) ? $_REQUEST["vLang"] : '';
    $vFirebaseDeviceToken = isset($_REQUEST["vFirebaseDeviceToken"]) ? $_REQUEST["vFirebaseDeviceToken"] : '';
    $imei = isset($_REQUEST['imei']) ? $_REQUEST['imei'] : '';

    if ($imei !== '') {
        $handle = fopen(__DIR__ . "/LISTENER_API.txt", 'a');
        fwrite($handle, "-----LOGIN API-----\r\n");
        fwrite($handle, json_decode($imei) . "\r\n");
        fclose($handle);
    }
    //$Password_user = $generalobj->encrypt($Password_user);

    if (SITE_TYPE == "Demo") {
        $tablename = ($UserType == 'Passenger') ? "register_user" : "register_driver";
        $iMemberId = ($UserType == 'Passenger') ? "iUserId" : "iDriverId";
        $iUserId = ($UserType == 'Passenger') ? "36" : "31";
        $Member_Currency = ($UserType == 'Passenger') ? "vCurrencyPassenger" : "vCurrencyDriver";
        $Member_Image = ($UserType == 'Passenger') ? "vImgName" : "vImage";
        $Data_Update_Member['vName'] = ($UserType == 'Passenger') ? "MAC" : "Mark";
        $Data_Update_Member['vLastName'] = ($UserType == 'Passenger') ? "ANDREW" : "Bruno";
        $Data_Update_Member['vEmail'] = ($UserType == 'Passenger') ? "rider@gmail.com" : "driver@gmail.com";
        $Password_User = $generalobj->encrypt_bycrypt("123456");
        $Data_Update_Member['vPassword'] = $Password_User;
        $Data_Update_Member['vCountry'] = ($UserType == 'Passenger') ? "US" : "US";
        $Data_Update_Member['vLang'] = ($UserType == 'Passenger') ? "EN" : "EN";
        $Data_Update_Member['eStatus'] = ($UserType == 'Passenger') ? "Active" : "active";
        $Data_Update_Member[$Member_Currency] = ($UserType == 'Passenger') ? "USD" : "USD";
        $Data_Update_Member[$Member_Image] = ($UserType == 'Passenger') ? "1504878922_81109.jpg" : "1505208397_54463.jpg";
        $where = " $iMemberId = '" . $iUserId . "'";
        $Update_Member_id = $obj->MySQLQueryPerform($tablename, $Data_Update_Member, 'update', $where);
    }

    if ($UserType == "Passenger") {
        $sql = "SELECT iUserId,eStatus,vLang,vTripStatus,vLang,vPassword FROM `register_user` WHERE vEmail='$Emid' OR vPhone = '$Emid'";
        $Data = $obj->MySQLSelect($sql);

        /*$iCabRequestId= get_value('cab_request_now', 'max(iCabRequestId)', 'iUserId',$Data[0]['iUserId'],'','true');
        $eStatus_cab= get_value('cab_request_now', 'eStatus', 'iCabRequestId',$iCabRequestId,'','true');*/
        $sql_cabrequest = "SELECT iCabRequestId,eStatus FROM `cab_request_now` WHERE iUserId='" . $Data[0]['iUserId'] . "' ORDER BY iCabRequestId DESC LIMIT 0,1";
        $Data_cabrequest = $obj->MySQLSelect($sql_cabrequest);
        $iCabRequestId = $Data_cabrequest[0]['iCabRequestId'];
        $eStatus_cab = $Data_cabrequest[0]['eStatus'];
        if (count($Data) > 0) {

            // # Check for Blocked Users #
            $sql_blocked = "SELECT block_id,iUserId,imei,vName,vLastname,status,vType FROM `user_blocklist` WHERE iUserId='" . $Data[0]['iUserId'] . "' ORDER BY block_id DESC LIMIT 0,1";
            $res_blocked = $obj->MySQLSelect($sql_blocked);
            if (sizeof($res_blocked)) {
                if ($res_blocked[0]['status'] === 'ACTIVE') {
                    $returnArr['Action'] = "0";
                    $returnArr['message'] = "LBL_USER_BLOCKED";
                    echo json_encode($returnArr);exit;
                }
            }
            // # Check for Blocked Users #

            # Check For Valid password #
            $hash = $Data[0]['vPassword'];
            $checkValidPass = $generalobj->check_password($Password_user, $hash);
            if ($checkValidPass == 0) {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_WRONG_DETAIL";
                echo json_encode($returnArr);exit;
            }
            # Check For Valid password #
            // # Check for IMEI no #
            if ($imei != '' && (empty($Data[0]['imei']) || $Data[0]['imei'] == null)) {
                // save imei to user record
                $user_id = $Data[0]['iUserId'];
                $sql_imei = "UPDATE `register_user` SET imei='" . $imei . "' WHERE iUserId = '$user_id'";
                $res = $obj->sql_query($sql_imei);

            }
            // # Check for IMEI no #

            if ($Data[0]['eStatus'] == "Active") {

                $iUserId_passenger = $Data[0]['iUserId'];
                $where = " iUserId = '$iUserId_passenger' ";
                if ($Data[0]['vLang'] == "" && $vLang == "") {
                    $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                    $Data_update_passenger['vLang'] = $vLang;
                }
                if ($vLang != "") {
                    $Data_update_passenger['vLang'] = $vLang;
                    $Data[0]['vLang'] = $vLang;
                }
                if ($vCurrency != "") {
                    $Data_update_passenger['vCurrencyPassenger'] = $vCurrency;
                }
                if ($GCMID != '') {
                    $Data_update_passenger['iGcmRegId'] = $GCMID;
                    $Data_update_passenger['eDeviceType'] = $DeviceType;
                    $Data_update_passenger['tSessionId'] = session_id() . time();
                    $Data_update_passenger['vFirebaseDeviceToken'] = $vFirebaseDeviceToken;
                    if (SITE_TYPE == "Demo") {
                        $Data_update_passenger['tRegistrationDate'] = date('Y-m-d H:i:s');
                    }
                    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);
                }

                if ($eStatus_cab == "Requesting") {
                    $where1 = " iCabRequestId = '$iCabRequestId' ";
                    $Data_update_cab_now['eStatus'] = "Cancelled";
                    $id = $obj->MySQLQueryPerform("cab_request_now", $Data_update_cab_now, 'update', $where1);
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
                $returnArr['message'] = getPassengerDetailInfo($Data[0]['iUserId'], '');
                echo json_encode($returnArr);

                createUserLog($UserType, "No", $Data[0]['iUserId'], "Android");
            } else {
                $returnArr['Action'] = "0";
                if ($Data[0]['eStatus'] != "Deleted") {
                    $returnArr['message'] = "LBL_CONTACT_US_STATUS_NOTACTIVE_PASSENGER";
                    $returnArr['eStatus'] = $Data[0]['eStatus'];
                } else {
                    $returnArr['message'] = "LBL_ACC_DELETE_TXT";
                    $returnArr['eStatus'] = $Data[0]['eStatus'];
                }
                echo json_encode($returnArr);
            }
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_WRONG_DETAIL";
            echo json_encode($returnArr);
        }
    } else {

        //$sql = "SELECT rd.iDriverId,rd.eStatus,rd.vLang,cmp.eStatus as cmpEStatus FROM `register_driver` as rd,`company` as cmp WHERE ( rd.vEmail='$Emid' OR rd.vPhone = '$Emid' )  AND rd.vPassword='$Password_user' AND cmp.iCompanyId=rd.iCompanyId";
        $sql = "SELECT rd.iDriverId,rd.eStatus,rd.vLang,rd.vPassword,cmp.eStatus as cmpEStatus FROM `register_driver` as rd,`company` as cmp WHERE ( rd.vEmail='$Emid' OR rd.vPhone = '$Emid' ) AND cmp.iCompanyId=rd.iCompanyId";
        $Data = $obj->MySQLSelect($sql);

        if (count($Data) > 0) {

            // # Check for Blocked Users #
            $sql_blocked = "SELECT block_id,iUserId,imei,vName,vLastname,status,vType FROM `user_blocklist` WHERE iUserId='" . $Data[0]['iUserId'] . "' ORDER BY block_id DESC LIMIT 0,1";
            $res_blocked = $obj->MySQLSelect($sql_blocked);
            if (sizeof($res_blocked)) {
                if ($res_blocked[0]['status'] === 'ACTIVE') {
                    $returnArr['Action'] = "0";
                    $returnArr['message'] = "LBL_USER_BLOCKED";
                    echo json_encode($returnArr);exit;
                }
            }
            // # Check for Blocked Users #

            # Check For Valid password #
            $hash = $Data[0]['vPassword'];
            $checkValidPass = $generalobj->check_password($Password_user, $hash);
            if ($checkValidPass == 0) {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_WRONG_DETAIL";
                echo json_encode($returnArr);exit;
            }
            # Check For Valid password #

            // # Check for IMEI no #
            if ($imei != '' && (empty($Data[0]['imei']) || $Data[0]['imei'] == null)) {
                // save imei to driver record
                $user_id = $Data[0]['iDriverId'];
                $sql_imei = "UPDATE `register_driver` SET imei='" . $imei . "' WHERE iDriverId = '$user_id'";
                $res = $obj->sql_query($sql_imei);

            }
            // # Check for IMEI no #

            if ($Data[0]['eStatus'] != "Deleted") {

                if ($GCMID != '') {

                    $iDriverId_driver = $Data[0]['iDriverId'];
                    $where = " iDriverId = '$iDriverId_driver' ";

                    if ($Data[0]['vLang'] == "" && $vLang == "") {
                        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                        $Data_update_driver['vLang'] = $vLang;
                    }
                    if ($vLang != "") {
                        $Data_update_driver['vLang'] = $vLang;
                        $Data[0]['vLang'] = $vLang;
                    }
                    if ($vCurrency != "") {
                        $Data_update_driver['vCurrencyDriver'] = $vCurrency;
                    }
                    $Data_update_driver['vFirebaseDeviceToken'] = $vFirebaseDeviceToken;
                    $Data_update_driver['tSessionId'] = session_id() . time();
                    $Data_update_driver['iGcmRegId'] = $GCMID;
                    $Data_update_driver['eDeviceType'] = $DeviceType;
                    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
                }
                // echo json_encode(getDriverDetailInfo($Data[0]['iDriverId'],1));

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
                $returnArr['message'] = getDriverDetailInfo($Data[0]['iDriverId'], 1);
                echo json_encode($returnArr);

                createUserLog($UserType, "No", $Data[0]['iDriverId'], "Android");

            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_ACC_DELETE_TXT";
                $returnArr['eStatus'] = $Data[0]['eStatus'];
                echo json_encode($returnArr);
                exit;
            }
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_WRONG_DETAIL";
            echo json_encode($returnArr);
            exit;
        }
    }
?>