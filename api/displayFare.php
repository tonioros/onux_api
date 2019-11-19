<?php 
    global $currency_supported_paypal, $generalobj;

    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';

    $tableName = $userType != "Driver" ? "register_user" : "register_driver";
    $iMemberId_KEY = $userType != "Driver" ? "iUserId" : "iDriverId";

    if ($iTripId == "") {
        $iTripId = get_value($tableName, 'iTripId', $iMemberId_KEY, $iMemberId, '', 'true');
    }

    //$ENABLE_TIP_MODULE=$generalobj->getConfigurations("configurations","ENABLE_TIP_MODULE");
    $vTripPaymentMode = get_value('trips', 'vTripPaymentMode', 'iTripId', $iTripId, '', 'true');
    $eType = get_value('trips', 'eType', 'iTripId', $iTripId, '', 'true');
    if ($vTripPaymentMode == "Card" && $eType == "Ride") {
        $result_fare['ENABLE_TIP_MODULE'] = $ENABLE_TIP_MODULE;
    } else {
        $result_fare['ENABLE_TIP_MODULE'] = "No";
    }
    $result_fare['FormattedTripDate'] = date('dS M Y \a\t h:i a', strtotime($result_fare[0]['tStartDate']));
    $result_fare['PayPalConfiguration'] = "No";
    $result_fare['DefaultCurrencyCode'] = "USD";
    $result_fare['PaypalFare'] = strval($result_fare[0]['TotalFare']);
    $result_fare['PaypalCurrencyCode'] = $vCurrencyCode;
    //$result_fare['APP_TYPE'] = $generalobj->getConfigurations("configurations","APP_TYPE");
    $result_fare['APP_TYPE'] = $APP_TYPE;
    /*if($result_fare['APP_TYPE'] == "UberX"){
    $result_fare['APP_DESTINATION_MODE'] = "None";
    }else{
    $result_fare['APP_DESTINATION_MODE'] = "Strict";
    }*/
    $result_fare['APP_DESTINATION_MODE'] = $APP_DESTINATION_MODE;
    // $result_fare['APP_DESTINATION_MODE'] = $generalobj->getConfigurations("configurations","APP_DESTINATION_MODE");
    $returnArr = gettrippricedetails($iTripId, $iMemberId, $userType, "DISPLAY");

    $result_fare = array_merge($result_fare, $returnArr);

    if (count($returnArr) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $result_fare;
    } else {
        $returnArr['Action'] = "0";
    }
    //echo "<pre>" ; print_r($returnArr); exit;
    echo json_encode($returnArr);



?>