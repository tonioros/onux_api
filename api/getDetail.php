<?php

    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $GCMID = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $deviceType = isset($_REQUEST["vDeviceType"]) ? $_REQUEST["vDeviceType"] : 'Android';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $vLangCode = isset($_REQUEST["vLang"]) ? $_REQUEST["vLang"] : '';

    if ($UserType == "Passenger") {
        $sql = "SELECT iGcmRegId,vTripStatus,vLang,eChangeLang FROM `register_user` WHERE iUserId='$iUserId'";
        $Data = $obj->MySQLSelect($sql);

        /*$iCabRequestId= get_value('cab_request_now', 'max(iCabRequestId)', 'iUserId',$iUserId,'','true');
        $eStatus_cab= get_value('cab_request_now', 'eStatus', 'iCabRequestId',$iCabRequestId,'','true');*/
        $sql_cab = "SELECT iCabRequestId,eStatus FROM cab_request_now WHERE iUserId = '" . $iUserId . "' ORDER BY iCabRequestId DESC LIMIT 0,1";
        $Data_cab = $obj->MySQLSelect($sql_cab);
        $iCabRequestId = $Data_cab[0]['iCabRequestId'];
        $eStatus_cab = $Data_cab[0]['eStatus'];
        if (count($Data) > 0) {

            ## Check and update Session ID ##
            /*$where = " iUserId = '".$iUserId."'";
            $Update_Session['tSessionId'] = session_id().time();
            $Update_Session_id = $obj->MySQLQueryPerform("register_user", $Update_Session, 'update', $where);*/
            ## Check and update Session ID ##

            $iGCMregID = $Data[0]['iGcmRegId'];
            $vTripStatus = $Data[0]['vTripStatus'];

            // if($GCMID!=''){

            // if($iGCMregID != $GCMID){
            // $where = " iUserId = '$iUserId' ";

            // $Data_update_passenger['iGcmRegId']=$GCMID;
            // $Data_update_passenger['eDeviceType']=$deviceType;

            // $id = $obj->MySQLQueryPerform("register_user",$Data_update_passenger,'update',$where);
            // }

            // }

            if ($GCMID != "" && $GCMID != $iGCMregID) {
                $returnArr['Action'] = "0";
                $returnArr['eStatus'] = "";
                $returnArr['message'] = "SESSION_OUT";
                echo json_encode($returnArr);
                exit;
            }

            if ($Data[0]['vLang'] == "") {
                $where = " iUserId = '$iUserId' ";
                $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                $Data_update_passenger['vLang'] = $vLang;
                $updateid = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);
                $Data[0]['vLang'] = $vLang;
            }

            if ($eStatus_cab == "Requesting") {
                $where = " iCabRequestId = '$iCabRequestId' ";

                $Data_update_cab_now['eStatus'] = "Cancelled";

                $id = $obj->MySQLQueryPerform("cab_request_now", $Data_update_cab_now, 'update', $where);
            }
            if (($vLangCode != $Data[0]['vLang']) || $Data[0]['eChangeLang'] == "Yes") {
                $returnArr['changeLangCode'] = "Yes";
                $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($Data[0]['vLang'], "1");
                $returnArr['vLanguageCode'] = $Data[0]['vLang'];
                $sql_LangCode = "SELECT eDirectionCode,vGMapLangCode FROM language_master WHERE `vCode` = '" . $Data[0]['vLang'] . "' ";
                $Data_checkLangCode = $obj->MySQLSelect($sql_LangCode);
                $returnArr['langType'] = $Data_checkLangCode[0]['eDirectionCode'];
                $returnArr['vGMapLangCode'] = $Data_checkLangCode[0]['vGMapLangCode'];
                $where = " iUserId = '$iUserId' ";
                $Data_update_passenger_lang['eChangeLang'] = "No";
                $updateLangid = $obj->MySQLQueryPerform("register_user", $Data_update_passenger_lang, 'update', $where);
                $Data[0]['eChangeLang'] = "No";
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

            $returnArr['Action'] = "1";
            $returnArr['message'] = getPassengerDetailInfo($iUserId, '');

            createUserLog($UserType, "Yes", $iUserId, "Android");

        } else {
            $returnArr['Action'] = "0";
            $returnArr['eStatus'] = "";
            $returnArr['message'] = "SESSION_OUT";
        }

        echo json_encode($returnArr);
    } else {
        $sql = "SELECT iGcmRegId,vLang,eChangeLang FROM `register_driver` WHERE iDriverId='$iUserId'";
        $Data = $obj->MySQLSelect($sql);

        if (count($Data) > 0) {

            $iGCMregID = $Data[0]['iGcmRegId'];

            ## Check and update Session ID ##
            /*$where = " iDriverId = '$iUserId' ";
            $Update_Session['tSessionId'] = session_id().time();
            $Update_Session_id = $obj->MySQLQueryPerform("register_driver", $Update_Session, 'update', $where);  */
            ## Check and update Session ID ##

            if ($Data[0]['vLang'] == "") {
                $where = " iDriverId = '$iUserId' ";
                $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                $Data_update_driver['vLang'] = $vLang;
                $updateid = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
            }

            // if($GCMID!=''){

            // if($iGCMregID!=$GCMID){
            // $where = " iDriverId = '$iUserId' ";

            // $Data_update_driver['iGcmRegId']=$GCMID;

            // $id = $obj->MySQLQueryPerform("register_driver",$Data_update_driver,'update',$where);
            // }

            // }
            if ($GCMID != "" && $GCMID != $iGCMregID) {
                $returnArr['Action'] = "0";
                $returnArr['eStatus'] = "";
                $returnArr['message'] = "SESSION_OUT";
                echo json_encode($returnArr);
                exit;
            }
            if (($vLangCode != $Data[0]['vLang']) || $Data[0]['eChangeLang'] == "Yes") {
                $returnArr['changeLangCode'] = "Yes";
                $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($Data[0]['vLang'], "1");
                $returnArr['vLanguageCode'] = $Data[0]['vLang'];
                $sql_LangCode = "SELECT eDirectionCode,vGMapLangCode FROM language_master WHERE `vCode` = '" . $Data[0]['vLang'] . "' ";
                $Data_checkLangCode = $obj->MySQLSelect($sql_LangCode);
                $returnArr['langType'] = $Data_checkLangCode[0]['eDirectionCode'];
                $returnArr['vGMapLangCode'] = $Data_checkLangCode[0]['vGMapLangCode'];
                $where = " iDriverId = '$iUserId' ";
                $Data_update_passenger_lang['eChangeLang'] = "No";
                $updateLangid = $obj->MySQLQueryPerform("register_driver", $Data_update_passenger_lang, 'update', $where);
                $Data[0]['eChangeLang'] = "No";
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

            $returnArr['Action'] = "1";
            $returnArr['message'] = getDriverDetailInfo($iUserId);

            createUserLog($UserType, "Yes", $iUserId, "Android");

        } else {
            $returnArr['Action'] = "0";
            $returnArr['eStatus'] = "";
            $returnArr['message'] = "SESSION_OUT";
        }

        echo json_encode($returnArr);

    }

