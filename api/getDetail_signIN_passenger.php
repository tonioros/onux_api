<?php 

    $Emid = isset($_REQUEST["Email"]) ? $_REQUEST["Email"] : '';
    $Password_user = isset($_REQUEST["Pass"]) ? $_REQUEST["Pass"] : '';
    $cityName = isset($_REQUEST["cityName"]) ? $_REQUEST["cityName"] : '';
    $GCMID = isset($_REQUEST["GCMID"]) ? $_REQUEST["GCMID"] : '';
    $vFirebaseDeviceToken = isset($_REQUEST["vFirebaseDeviceToken"]) ? $_REQUEST["vFirebaseDeviceToken"] : '';

    $Password_passenger = $generalobj->encrypt($Password_user);

    $DeviceType = "Android";

    $sql = "SELECT iUserId,eStatus,vLang,vTripStatus FROM `register_user` WHERE vEmail='$Emid'  && vPassword='$Password_passenger'";
    $Data = $obj->MySQLSelect($sql);

    /*$iCabRequestId= get_value('cab_request_now', 'max(iCabRequestId)', 'iUserId',$Data[0]['iUserId'],'','true');
    $eStatus_cab= get_value('cab_request_now', 'eStatus', 'iCabRequestId',$iCabRequestId,'','true');*/
    $sql_cabrequest = "SELECT iCabRequestId,eStatus FROM `cab_request_now` WHERE iUserId='" . $Data[0]['iUserId'] . "' ORDER BY iCabRequestId DESC LIMIT 0,1";
    $Data_cabrequest = $obj->MySQLSelect($sql_cabrequest);
    $iCabRequestId = $Data_cabrequest[0]['iCabRequestId'];
    $eStatus_cab = $Data_cabrequest[0]['eStatus'];
    if (count($Data) > 0) {
        if ($Data[0]['eStatus'] == "Active") {

            $iUserId_passenger = $Data[0]['iUserId'];
            $where = " iUserId = '$iUserId_passenger' ";

            if ($GCMID != '') {
                $Data_update_passenger['tSessionId'] = session_id() . time();
                $Data_update_passenger['iGcmRegId'] = $GCMID;
                $Data_update_passenger['eDeviceType'] = $DeviceType;
                $Data_update_passenger['vFirebaseDeviceToken'] = $vFirebaseDeviceToken;

                $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);
            }

            if ($eStatus_cab == "Requesting") {
                $where1 = " iCabRequestId = '$iCabRequestId' ";
                $Data_update_cab_now['eStatus'] = "Cancelled";

                $id = $obj->MySQLQueryPerform("cab_request_now", $Data_update_cab_now, 'update', $where1);
            }

            $returnArr['changeLangCode'] = "Yes";
            $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($Data[0]['vLang'], "1");

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

            $returnArr['ProfileData'] = getPassengerDetailInfo($Data[0]['iUserId'], $cityName);
            echo json_encode($returnArr);
        } else {
            if ($Data[0]['eStatus'] != "Deleted") {
                echo "LBL_CONTACT_US_STATUS_NOTACTIVE_PASSENGER";
            } else {
                echo "ACC_DELETED";
            }
        }

    } else {
        $sql = "SELECT * FROM `register_user` WHERE vEmail='$Emid'";
        $num_rows_Email = $obj->MySQLSelect($sql);
        if (count($num_rows_Email) == 1) {
            echo "LBL_PASSWORD_ERROR_TXT";
        } else {
            echo "LBL_NO_REG_FOUND";
        }
    }


?>