<?php 

    $fbid = isset($_REQUEST["fbid"]) ? $_REQUEST["fbid"] : '';
    $Fname = isset($_REQUEST["Fname"]) ? $_REQUEST["Fname"] : '';
    $Lname = isset($_REQUEST["Lname"]) ? $_REQUEST["Lname"] : '';
    $email = isset($_REQUEST["email"]) ? $_REQUEST["email"] : '';
    $GCMID = isset($_REQUEST["GCMID"]) ? $_REQUEST["GCMID"] : '';
    $phone_mobile = isset($_REQUEST["phone"]) ? $_REQUEST["phone"] : '';
    $CountryCode = isset($_REQUEST["CountryCode"]) ? $_REQUEST["CountryCode"] : '';
    $PhoneCode = isset($_REQUEST["PhoneCode"]) ? $_REQUEST["PhoneCode"] : '';
    $vFirebaseDeviceToken = isset($_REQUEST["vFirebaseDeviceToken"]) ? $_REQUEST["vFirebaseDeviceToken"] : '';

    // $Language_Code=($obj->MySQLSelect("SELECT `vCode` FROM `language_master` WHERE `eDefault`='Yes'")[0]['vCode']);
    $Language_Code = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');

    $deviceType = "Android";

    $sql = "SELECT * FROM `register_user` WHERE vEmail = '$email' OR vPhone = '$phone_mobile'";
    $check_passenger = $obj->MySQLSelect($sql);

    if (count($check_passenger) > 0) {
        if ($email == $check_passenger[0]['vEmail']) {
            echo "EMAIL_EXIST";
        } else {
            echo "MOBILE_EXIST";
        }
    } else {

        $Data_passenger['vFbId'] = $fbid;
        $Data_passenger['vName'] = $Fname;
        $Data_passenger['vLastName'] = $Lname;
        $Data_passenger['vEmail'] = $email;
        $Data_passenger['vPhone'] = $phone_mobile;
        $Data_passenger['vPassword'] = '';
        $Data_passenger['iGcmRegId'] = $GCMID;
        $Data_passenger['vLang'] = $Language_Code;
        $Data_passenger['vPhoneCode'] = $PhoneCode;
        $Data_passenger['vCountry'] = $CountryCode;
        $Data_passenger['vFirebaseDeviceToken'] = $vFirebaseDeviceToken;
        $Data_passenger['eDeviceType'] = $deviceType;
        // $Data_passenger['vCurrencyPassenger']=($obj->MySQLSelect("SELECT vName FROM currency WHERE eDefault='Yes'")[0]['vName']);
        $Data_passenger['vCurrencyPassenger'] = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');

        $id = $obj->MySQLQueryPerform("register_user", $Data_passenger, 'insert');

        if ($id > 0) {
            /*new added*/

            echo json_encode(getPassengerDetailInfo($id, $cityName));

            $maildata['EMAIL'] = $email;
            $maildata['NAME'] = $Fname;
            $maildata['PASSWORD'] = $password;
            $generalobj->send_email_user("MEMBER_REGISTRATION_USER", $maildata);
        } else {
            echo "Registration UnSuccessful.";
        }
    }

?>