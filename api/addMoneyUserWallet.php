<?php 
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger'; //Passenger,Driver
    $fAmount = isset($_REQUEST["fAmount"]) ? $_REQUEST["fAmount"] : '';
    if ($eMemberType == "Passenger") {
        $tbl_name = "register_user";
        $currencycode = "vCurrencyPassenger";
        $iUserId = "iUserId";
        $eUserType = "Rider";
    } else {
        $tbl_name = "register_driver";
        $currencycode = "vCurrencyDriver";
        $iUserId = "iDriverId";
        $eUserType = "Driver";
    }
    /*$vStripeCusId = get_value($tbl_name, 'vStripeCusId', $iUserId, $iMemberId,'','true');
    $vStripeToken = get_value($tbl_name, 'vStripeToken', $iUserId, $iMemberId,'','true');
    $userCurrencyCode = get_value($tbl_name, $currencycode, $iUserId, $iMemberId,'','true');*/
    $UserCardData = get_value($tbl_name, 'vStripeCusId,vStripeToken,vBrainTreeToken,vPaymayaCustId,vXenditToken,vPaymayaToken,' . $currencycode . ' as currencycode', $iUserId, $iMemberId);
    $vStripeCusId = $UserCardData[0]['vStripeCusId'];
    $vStripeToken = $UserCardData[0]['vStripeToken'];
    $userCurrencyCode = $UserCardData[0]['currencycode'];
    $vBrainTreeToken = $UserCardData[0]['vBrainTreeToken'];
    $vPaymayaCustId = $UserCardData[0]['vPaymayaCustId'];
    $vPaymayaToken = $UserCardData[0]['vPaymayaToken'];
    $vXenditToken = $UserCardData[0]['vXenditToken'];
    $userCurrencyRatio = get_value('currency', 'Ratio', 'vName', $userCurrencyCode, '', 'true');
    $walletamount = round($fAmount / $userCurrencyRatio, 2);
    /*$currencyCode = get_value('currency', 'vName', 'eDefault', 'Yes','','true');
    $currencyratio = get_value('currency', 'Ratio', 'vName', $currencyCode,'','true');*/
    $DefaultCurrencyData = get_value('currency', 'vName,Ratio', 'eDefault', 'Yes');
    $currencyCode = $DefaultCurrencyData[0]['vName'];
    $currencyratio = $DefaultCurrencyData[0]['Ratio'];
    $price = $fAmount * $currencyratio;
    $price_new = $walletamount * 100;
    $price_new = round($price_new);
    if ((($vStripeCusId == "" || $vStripeToken == "") && $APP_PAYMENT_METHOD == "Stripe")) {
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

    $dDate = Date('Y-m-d H:i:s');
    $eFor = 'Deposit';
    $eType = 'Credit';
    $iTripId = 0;
    //$tDescription = "Amount credited";
    $tDescription = '#LBL_AMOUNT_CREDIT#';
    $ePaymentStatus = 'Unsettelled';
    $t_rand_nun = rand(1111111, 9999999);
    $Charge_Array = array("iFare" => $walletamount, "price_new" => $price_new, "currency" => $currencyCode, "vStripeCusId" => $vStripeCusId, "description" => $tDescription, "iTripId" => 0, "eCancelChargeFailed" => "No", "vBrainTreeToken" => $vBrainTreeToken, "vRideNo" => $t_rand_nun, "iMemberId" => $iMemberId, "UserType" => $eMemberType);
    $ChargeidArr = ChargeCustomer($Charge_Array, "addMoneyUserWallet"); // function for charge customer
    $ChargeidArrId = $ChargeidArr['id'];
    $status = $ChargeidArr['status'];
    if ($status == "success") {
        $generalobj->InsertIntoUserWallet($iMemberId, $eUserType, $walletamount, 'Credit', 0, $eFor, $tDescription, $ePaymentStatus, $dDate);
        //$user_available_balance = $generalobj->get_user_available_balance($iMemberId,$eUserType);
        $user_available_balance = $generalobj->get_user_available_balance_app_display($iMemberId, $eUserType);
        $returnArr["Action"] = "1";
        //$returnArr["MemberBalance"] = strval($generalobj->userwalletcurrency(0,$user_available_balance,$userCurrencyCode));
        $returnArr["MemberBalance"] = strval($user_available_balance);
        $returnArr['message1'] = "LBL_WALLET_MONEY_CREDITED";

        if ($eMemberType != "Driver") {
            $returnArr['message'] = getPassengerDetailInfo($iMemberId, "");
        } else {
            $returnArr['message'] = getDriverDetailInfo($iMemberId);
        }
        echo json_encode($returnArr);exit;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_WALLET_MONEY_CREDITED_FAILED";
        echo json_encode($returnArr);exit;
    }
   


?>