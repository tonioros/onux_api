<?php 
    
    $fbid = isset($_REQUEST["vFbId"]) ? $_REQUEST["vFbId"] : '';
    $Fname = isset($_REQUEST["vFirstName"]) ? $_REQUEST["vFirstName"] : '';
    $Lname = isset($_REQUEST["vLastName"]) ? $_REQUEST["vLastName"] : '';
    $email = isset($_REQUEST["vEmail"]) ? $_REQUEST["vEmail"] : '';
    $email = strtolower($email);
    $phone_mobile = isset($_REQUEST["vPhone"]) ? $_REQUEST["vPhone"] : '';
    $password = isset($_REQUEST["vPassword"]) ? $_REQUEST["vPassword"] : '';
    $iGcmRegId = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $phoneCode = isset($_REQUEST["PhoneCode"]) ? $_REQUEST["PhoneCode"] : '';
    $CountryCode = isset($_REQUEST["CountryCode"]) ? $_REQUEST["CountryCode"] : '';
    $vInviteCode = isset($_REQUEST["vInviteCode"]) ? $_REQUEST["vInviteCode"] : '';
    $deviceType = isset($_REQUEST["vDeviceType"]) ? $_REQUEST["vDeviceType"] : 'Android';
    $vCurrency = isset($_REQUEST["vCurrency"]) ? $_REQUEST["vCurrency"] : '';
    $vLang = isset($_REQUEST["vLang"]) ? $_REQUEST["vLang"] : '';
    $user_type = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $eSignUpType = isset($_REQUEST["eSignUpType"]) ? $_REQUEST["eSignUpType"] : 'Normal';
    $vFirebaseDeviceToken = isset($_REQUEST["vFirebaseDeviceToken"]) ? $_REQUEST["vFirebaseDeviceToken"] : '';
    $vImageURL = isset($_REQUEST["vImageURL"]) ? $_REQUEST["vImageURL"] : '';
    $imei = isset($_REQUEST['imei']) ? $_REQUEST['imei'] : '';

    
    if ($email == "" && $phone_mobile == "" && $fbid == "") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        echo json_encode($returnArr);exit;
    }
    if ($vCurrency == '') {
        $vCurrency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    }
    if ($vLang == '') {
        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $first = substr($phone_mobile, 0, 1);
    if ($first == "0") {
        $phone_mobile = substr($phone_mobile, 1);
    }

    if ($fbid != "") {
        if ($Lname == "" || $Lname == null) {
            $username = explode(" ", $Fname);
            if ($username[1] != "") {
                $Fname = $username[0];
                $Lname = $username[1];
            }
        }
    }

    if ($user_type == "Passenger") {
        $tblname = "register_user";
        $eRefType = "Rider";
        $Data_passenger['vPhoneCode'] = $phoneCode;
        $Data_passenger['vCurrencyPassenger'] = $vCurrency;
        $vImage = 'vImgName';
        $iMemberId = 'iUserId';
    } else {
        $tblname = "register_driver";
        $eRefType = "Driver";
        $Data_passenger['vCode'] = $phoneCode;
        $Data_passenger['vCurrencyDriver'] = $vCurrency;
        $Data_passenger['iCompanyId'] = 1;
        $vImage = 'vImage';
        $iMemberId = 'iDriverId';
    }

    //$sql = "SELECT * FROM `register_user` WHERE vEmail = '$email' OR vPhone = '$phone_mobile'";
    $sql = "SELECT * FROM $tblname WHERE 1=1 AND IF('$email'!='',vEmail = '$email',0) OR IF('$phone_mobile'!='',vPhone = '$phone_mobile',0) OR IF('$fbid'!='',vFbId = '$fbid',0)";
    $check_passenger = $obj->MySQLSelect($sql);

    //$Password_passenger = $generalobj->encrypt($password);
    if ($password != "") {
        $Password_passenger = $generalobj->encrypt_bycrypt($password);
    } else {
        $Password_passenger = "";
    }

    if (count($check_passenger) > 0) {
        $returnArr['Action'] = "0";

        if ($check_passenger[0]['eStatus'] == "Deleted") {
            $returnArr['message'] = "LBL_ACCOUNT_STATUS_DELETED_TXT";
            echo json_encode($returnArr);exit;
        }

        if ($email == strtolower($check_passenger[0]['vEmail'])) {
            $returnArr['message'] = "LBL_ALREADY_REGISTERED_TXT";
        } else {
            $returnArr['message'] = "LBL_MOBILE_EXIST";
        }
        echo json_encode($returnArr);exit;
    } else {
        $check_inviteCode = "";
        $inviteSuccess = false;
        if ($vInviteCode != "") {
            $check_inviteCode = $generalobj->validationrefercode($vInviteCode);
            if ($check_inviteCode == "" || $check_inviteCode == "0" || $check_inviteCode == 0) {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_INVITE_CODE_INVALID";
                echo json_encode($returnArr);
                exit;
            } else {
                $inviteRes = explode("|", $check_inviteCode);
                $Data_passenger['iRefUserId'] = $inviteRes[0];
                $Data_passenger['eRefType'] = $inviteRes[1];
                $inviteSuccess = true;
            }
        }

        $Data_passenger['vFbId'] = $fbid;
        $Data_passenger['vName'] = $Fname;
        $Data_passenger['vLastName'] = $Lname;
        $Data_passenger['vEmail'] = $email;
        $Data_passenger['imei'] = $imei;
        $Data_passenger['vPhone'] = $phone_mobile;
        $Data_passenger['vPassword'] = $Password_passenger;
        $Data_passenger['iGcmRegId'] = $iGcmRegId;
        $Data_passenger['vFirebaseDeviceToken'] = $vFirebaseDeviceToken;
        $Data_passenger['vLang'] = $vLang;
        //$Data_passenger['vPhoneCode']=$phoneCode;
        $Data_passenger['vCountry'] = $CountryCode;
        $Data_passenger['eDeviceType'] = $deviceType;
        $Data_passenger['vRefCode'] = $generalobj->ganaraterefercode($eRefType);
        //$Data_passenger['vCurrencyPassenger']=$vCurrency;
        $Data_passenger['dRefDate'] = @date('Y-m-d H:i:s');
        $Data_passenger['tRegistrationDate'] = @date('Y-m-d H:i:s');
        $Data_passenger['eSignUpType'] = $eSignUpType;
        if ($eSignUpType == "Facebook" || $eSignUpType == "Google") {
            $Data_passenger['eEmailVerified'] = "Yes";
        }
        $random = substr(md5(rand()), 0, 7);
        $Data_passenger['tDeviceSessionId'] = session_id() . time() . $random;
        $Data_passenger['tSessionId'] = session_id() . time();

        if (SITE_TYPE == 'Demo') {
            $Data_passenger['eStatus'] = 'Active';
            $Data_passenger['eEmailVerified'] = 'Yes';
            $Data_passenger['ePhoneVerified'] = 'Yes';
        }
        $id = $obj->MySQLQueryPerform($tblname, $Data_passenger, 'insert');
        ## Upload Image of Member if SignUp from Google, Facebook Or Twitter ##
        if ($fbid != 0 || $fbid != "") {
            $UserImage = UploadUserImage($id, $user_type, $eSignUpType, $fbid, $vImageURL);
            if ($UserImage != "") {
                $where = " $iMemberId = '$id' ";
                $Data_update_image_member[$vImage] = $UserImage;
                $imageuploadid = $obj->MySQLQueryPerform($tblname, $Data_update_image_member, 'update', $where);
            }
        }
        ## Upload Image of Member if SignUp from Google, Facebook Or Twitter ##
        $returnArr['changeLangCode'] = "Yes";
        $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($vLang, "1");
        $returnArr['vLanguageCode'] = $vLang;

        $sql_LangCode = "SELECT eDirectionCode,vGMapLangCode FROM language_master WHERE `vCode` = '" . $vLang . "' ";
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

        if (strtolower($user_type) == 'driver' && SITE_TYPE == 'Live') {
            if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') {
                $query = "SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` AND eType = 'UberX'";
                $result = $obj->MySQLSelect($query);

                $Drive_vehicle['iDriverId'] = $id;
                $Drive_vehicle['iCompanyId'] = "1";
                $Drive_vehicle['iMakeId'] = "3";
                $Drive_vehicle['iModelId'] = "1";
                $Drive_vehicle['iYear'] = Date('Y');
                $Drive_vehicle['vLicencePlate'] = "My Services";
                $Drive_vehicle['eStatus'] = "Active";
                $Drive_vehicle['eCarX'] = "Yes";
                $Drive_vehicle['eCarGo'] = "Yes";
                $Drive_vehicle['eType'] = "UberX";
                $Drive_vehicle['vCarType'] = "";
                $iDriver_VehicleId = $obj->MySQLQueryPerform('driver_vehicle', $Drive_vehicle, 'insert');
                if ($APP_TYPE == 'UberX') {
                    $sql = "UPDATE register_driver set iDriverVehicleId='" . $iDriver_VehicleId . "' WHERE iDriverId='" . $id . "'";
                    $obj->sql_query($sql);
                }
            }
        }

        if (strtolower($user_type) == 'driver' && SITE_TYPE == 'Demo') {
            $Drive_vehicle['iDriverId'] = $id;
            $Drive_vehicle['iCompanyId'] = "1";
            $Drive_vehicle['iMakeId'] = "3";
            $Drive_vehicle['iModelId'] = "1";
            $Drive_vehicle['iYear'] = Date('Y');
            $Drive_vehicle['eStatus'] = "Active";
            $Drive_vehicle['eCarX'] = "Yes";
            $Drive_vehicle['eCarGo'] = "Yes";
            if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX') {
                $Drive_vehicle['vLicencePlate'] = "My Services";
                $Drive_vehicle['eType'] = "UberX";

                $query = "SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` WHERE eType = 'UberX'";
                $result = $obj->MySQLSelect($query);
                $Drive_vehicle['vCarType'] = $result[0]['countId'];
                $Drive_vehicle['vRentalCarType'] = $result[0]['countId'];
                $iDriver_VehicleId = $obj->MySQLQueryPerform('driver_vehicle', $Drive_vehicle, 'insert');
                if ($APP_TYPE == 'UberX') {
                    $sql = "UPDATE register_driver set iDriverVehicleId='" . $iDriver_VehicleId . "' WHERE iDriverId='" . $id . "'";
                    $obj->sql_query($sql);
                }
           

                if ($APP_TYPE == 'Ride-Delivery-UberX') {
                    $query = "SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` WHERE eType = 'Ride' OR eType = 'Deliver'";
                    $result_ride = $obj->MySQLSelect($query);
                    $Drive_vehicle_ride['iDriverId'] = $id;
                    $Drive_vehicle_ride['iCompanyId'] = "1";
                    $Drive_vehicle_ride['iYear'] = "2014";
                    $Drive_vehicle_ride['vLicencePlate'] = "CK201";
                    $Drive_vehicle_ride['eStatus'] = "Active";
                    $Drive_vehicle_ride['eCarX'] = "Yes";
                    $Drive_vehicle_ride['eCarGo'] = "Yes";
                    $Drive_vehicle_ride['eType'] = "Ride";
                    $Drive_vehicle_delivery = $Drive_vehicle_ride;
                    $Drive_vehicle_ride['iMakeId'] = "1";
                    $Drive_vehicle_ride['iModelId'] = "1";
                    $Drive_vehicle_ride['vCarType'] = $result_ride[0]['countId'];
                    $Drive_vehicle_ride['vRentalCarType'] = $result_ride[0]['countId'];
                    $iDriver_Ride_VehicleId = $obj->MySQLQueryPerform('driver_vehicle', $Drive_vehicle_ride, 'insert');

                    $sql = "UPDATE register_driver set iDriverVehicleId='" . $iDriver_Ride_VehicleId . "' WHERE iDriverId='" . $id . "'";
                    $obj->sql_query($sql);

                    $query = "SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` WHERE eType = 'Ride' OR eType = 'Deliver'";
                    $result_delivery = $obj->MySQLSelect($query);
                    $Drive_vehicle_delivery['iMakeId'] = "5";
                    $Drive_vehicle_delivery['iModelId'] = "18";
                    $Drive_vehicle_delivery['eType'] = "Delivery";
                    $Drive_vehicle_delivery['vCarType'] = $result_delivery[0]['countId'];
                    $Drive_vehicle_delivery['vRentalCarType'] = $result_delivery[0]['countId'];
                    $iDriver_Delivery_VehicleId = $obj->MySQLQueryPerform('driver_vehicle', $Drive_vehicle_delivery, 'insert');
                }

            } else {
                $query = "SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type`";
                $result = $obj->MySQLSelect($query);
                $Drive_vehicle['iDriverId'] = $id;
                $Drive_vehicle['iCompanyId'] = "1";
                $Drive_vehicle['iMakeId'] = "5";
                $Drive_vehicle['iModelId'] = "18";
                $Drive_vehicle['iYear'] = "2014";
                $Drive_vehicle['vLicencePlate'] = "CK201";
                $Drive_vehicle['eStatus'] = "Active";
                $Drive_vehicle['eCarX'] = "Yes";
                $Drive_vehicle['eCarGo'] = "Yes";
                $Drive_vehicle['vCarType'] = $result[0]['countId'];
                $Drive_vehicle['vRentalCarType'] = $result[0]['countId'];
                $iDriver_VehicleId = $obj->MySQLQueryPerform('driver_vehicle', $Drive_vehicle, 'insert');
                $sql = "UPDATE register_driver set iDriverVehicleId='" . $iDriver_VehicleId . "' WHERE iDriverId='" . $id . "'";
                $obj->sql_query($sql);
            }
        }

        if ($id > 0) {
            if ($inviteSuccess == true) {
                $eFor = "Referrer";
                $tDescription = "Referral amount credited";
                $dDate = Date('Y-m-d H:i:s');
                $ePaymentStatus = "Unsettelled";
            }

            /*new added*/
            $returnArr['Action'] = "1";
            if ($user_type == "Passenger") {
                $returnArr['message'] = getPassengerDetailInfo($id);
            } else {
                $returnArr['message'] = getDriverDetailInfo($id);
            }

            echo json_encode($returnArr);

            $maildata['EMAIL'] = $email;
            $maildata['NAME'] = $Fname;
            $maildata['PASSWORD'] = "Password: " . $password;
            $maildata['SOCIALNOTES'] = '';

            if ($user_type == "Passenger") {
                $generalobj->send_email_user("MEMBER_REGISTRATION_USER", $maildata);
            } else {
                $generalobj->send_email_user("DRIVER_REGISTRATION_USER", $maildata);
            }
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            echo json_encode($returnArr);
            exit;
        }
    }



