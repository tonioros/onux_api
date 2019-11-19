<?php 
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger'; //Passenger

    $sqld = "SELECT vStripeCusId,vStripeToken,vCurrencyPassenger,vBrainTreeToken,vPaymayaCustId,vPaymayaToken,vXenditToken FROM register_user WHERE iUserId = '" . $iMemberId . "'";
    $riderData = $obj->MySQLSelect($sqld);
    $vStripeCusId = $riderData[0]['vStripeCusId'];
    $vStripeToken = $riderData[0]['vStripeToken'];
    $vBrainTreeToken = $riderData[0]['vBrainTreeToken'];
    $vPaymayaCustId = $riderData[0]['vPaymayaCustId'];
    $vPaymayaToken = $riderData[0]['vPaymayaToken'];
    $vXenditToken = $riderData[0]['vXenditToken'];
    $fTripsOutStandingAmount = GetPassengerOutstandingAmount($iMemberId);
    $DefaultCurrencyData = get_value('currency', 'vName,Ratio', 'eDefault', 'Yes');
    $currencyCode = $DefaultCurrencyData[0]['vName'];
    $currencyratio = $DefaultCurrencyData[0]['Ratio'];
    $price_new = $fTripsOutStandingAmount * $currencyratio;
    $price_new = round($price_new * 100, 2);
    $tDescription = "Amount charge for trip oustanding balance";
    $t_rand_nun = rand(1111111, 9999999);

    if (($vStripeCusId == "" || $vStripeToken == "") && $APP_PAYMENT_METHOD == "Stripe") {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
        echo json_encode($returnArr);exit;
    }

    if ($vBrainTreeToken == "" && $APP_PAYMENT_METHOD == "Braintree") {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";

        echo json_encode($returnArr);exit;
    }
    if ((($vPaymayaCustId == "" || $vPaymayaToken == "") && $APP_PAYMENT_METHOD == "Paymaya")) {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
        echo json_encode($returnArr);exit;
    }
    if ($vXenditToken == "" && $APP_PAYMENT_METHOD == "Xendit") {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
        echo json_encode($returnArr);exit;
    }
    $Charge_Array = array("iFare" => $fTripsOutStandingAmount, "price_new" => $price_new, "currency" => $currencyCode, "vStripeCusId" => $vStripeCusId, "description" => $tDescription, "iTripId" => 0, "eCancelChargeFailed" => "No", "vBrainTreeToken" => $vBrainTreeToken, "vRideNo" => $t_rand_nun, "iMemberId" => $iMemberId, "UserType" => "Passenger");
    $ChargeidArr = ChargeCustomer($Charge_Array, "ChargePassengerOutstandingAmount"); // function for charge customer
    $ChargeidArrId = $ChargeidArr['id'];
    $status = $ChargeidArr['status'];
    if ($status == "success") {
        $updateQuery = "UPDATE register_user set fTripsOutStandingAmount = '0' WHERE iUserId = " . $iMemberId;
        $obj->sql_query($updateQuery);

        $updateQury = "UPDATE trip_outstanding_amount set ePaidByPassenger = 'Yes' WHERE iUserId = " . $iMemberId;
        $obj->sql_query($updateQury);

        $returnArr['Action'] = "1";
        $returnArr['message'] = getPassengerDetailInfo($iMemberId, "");
        $returnArr['message1'] = "LBL_OUTSTANDING_AMOUT_PAID_TXT";
        echo json_encode($returnArr);exit;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_CHARGE_COLLECT_FAILED1";

        echo json_encode($returnArr);exit;
    }

  
?>