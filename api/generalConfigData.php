<?php 
    include_once 'main.inc.php';
        $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
        $GeneralMemberId = isset($_REQUEST['GeneralMemberId']) ? trim($_REQUEST['GeneralMemberId']) : '';
        $vLang = isset($_REQUEST["vLang"]) ? $_REQUEST["vLang"] : '';
    
        $DataArr['LanguageLabels'] = getLanguageLabelsArr($vLang, "1");
        $DataArr['Action'] = "1";
    
        $sql = "SELECT vCode, vGMapLangCode, eDirectionCode as eType, vTitle,vCurrencyCode,vCurrencySymbol,eDefault  FROM  `language_master` WHERE  `eStatus` = 'Active' ORDER BY vTitle ASC ";
        $defLangValues = $obj->MySQLSelect($sql);
        $DataArr['LIST_LANGUAGES'] = $defLangValues;
        for ($i = 0; $i < count($defLangValues); $i++) {
            if ($defLangValues[$i]['eDefault'] == "Yes") {
                $DataArr['DefaultLanguageValues'] = $defLangValues[$i];
            }
        }
    
        if ($vLang != "") {
            $sql = "SELECT vCode, vGMapLangCode, eDirectionCode as eType, vTitle,vCurrencyCode,vCurrencySymbol,eDefault  FROM  `language_master` WHERE  `vCode` = '" . $vLang . "' ";
            $requireLangValues = $obj->MySQLSelect($sql);
            $DataArr['DefaultLanguageValues'] = $requireLangValues[0];
        }
    
        $sql = "SELECT iCurrencyId,vName, vSymbol,iDispOrder, eDefault,Ratio,fThresholdAmount,eStatus  FROM  `currency` WHERE  `eStatus` = 'Active' ";
        $defCurrencyValues = $obj->MySQLSelect($sql);
        $DataArr['LIST_CURRENCY'] = $defCurrencyValues;
        for ($i = 0; $i < count($defCurrencyValues); $i++) {
            if ($defCurrencyValues[$i]['eDefault'] == "Yes") {
                $DataArr['DefaultCurrencyValues'] = $defCurrencyValues[$i];
            }
        }
    
        for ($i = 0; $i < count($generalConfigArr); $i++) {
            $vName = $generalConfigArr[$i]["vName"];
            $vValue = $generalConfigArr[$i]["vValue"];
            $$vName = $vValue;
            $DataArr[$vName] = $vValue;
        }
        $DataArr['GOOGLE_ANALYTICS'] = "";
        $DataArr['FACEBOOK_IFRAME'] = "";
        if ($UserType == "Passenger") {
            $DataArr['LINK_FORGET_PASS_PAGE_PASSENGER'] = $tconfig["tsite_url"] . $LINK_FORGET_PASS_PAGE_PASSENGER;
            $DataArr['CONFIG_CLIENT_ID'] = $CONFIG_CLIENT_ID;
            $DataArr['FACEBOOK_LOGIN'] = $PASSENGER_FACEBOOK_LOGIN;
            $DataArr['GOOGLE_LOGIN'] = $PASSENGER_GOOGLE_LOGIN;
            $DataArr['TWITTER_LOGIN'] = $PASSENGER_TWITTER_LOGIN;
        } else {
            $DataArr['LINK_FORGET_PASS_PAGE_DRIVER'] = $tconfig["tsite_url"] . $LINK_FORGET_PASS_PAGE_DRIVER;
            $DataArr['LINK_SIGN_UP_PAGE_DRIVER'] = $tconfig["tsite_url"] . $LINK_SIGN_UP_PAGE_DRIVER;
            $DataArr['FACEBOOK_LOGIN'] = $DRIVER_FACEBOOK_LOGIN;
            $DataArr['GOOGLE_LOGIN'] = $DRIVER_GOOGLE_LOGIN;
            $DataArr['TWITTER_LOGIN'] = $DRIVER_TWITTER_LOGIN;
        }
        $DataArr['SERVER_MAINTENANCE_ENABLE'] = $MAINTENANCE_APPS;
        $DataArr['SITE_TYPE'] = SITE_TYPE;
        $usercountrydetailbytimezone = GetUserCounryDetail($GeneralMemberId, $UserType, $vTimeZone, $vUserDeviceCountry);
        $DataArr['vDefaultCountry'] = $usercountrydetailbytimezone['vDefaultCountry'];
        $DataArr['vDefaultCountryCode'] = $usercountrydetailbytimezone['vDefaultCountryCode'];
        $DataArr['vDefaultPhoneCode'] = $usercountrydetailbytimezone['vDefaultPhoneCode'];
        $DataArr['OPEN_SETTINGS_URL_SCHEMA'] = "A###p####!!!!!###p####!!!!###@@@@#######-Pr###@@@!!!!###ef####s:r##@@@@#oo###t=Se####tt###i@@@##n##@@g#s";
        $DataArr['OPEN_LOCATION_SETTINGS_URL_SCHEMA'] = "A##@@@##p#!!!!##p###-#P###!!!##r##!!!!#ef#!!!##@@##s:###@@@####ro##@@###!!!!###o###@@@#t=P####riv####!!!###ac####y&###!!!##p###a##!!!#t##h=L###O##CA#@@#TI##O#@#N";
    
        $obj->MySQLClose();
        echo json_encode($DataArr);
        exit;
?>