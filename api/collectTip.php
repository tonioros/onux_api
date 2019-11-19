<?php 
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $fAmount = isset($_REQUEST["fAmount"]) ? $_REQUEST["fAmount"] : '';

    $tbl_name = "register_user";
    $currencycode = "vCurrencyPassenger";
    $iUserId = "iUserId";
    $eUserType = "Rider";

    if ($iMemberId == "") {
        $iMemberId = get_value('trips', 'iUserId', 'iTripId', $iTripId, '', 'true');
    }
    /*$vStripeCusId = get_value($tbl_name, 'vStripeCusId', $iUserId, $iMemberId,'','true');
    $vStripeToken = get_value($tbl_name, 'vStripeToken', $iUserId, $iMemberId,'','true');
    $userCurrencyCode = get_value($tbl_name, $currencycode, $iUserId, $iMemberId,'','true'); */
    $UserData = get_value($tbl_name, 'vStripeCusId,vStripeToken,vCurrencyPassenger', $iUserId, $iMemberId);
    $vStripeCusId = $UserData[0]['vStripeCusId'];
    $vStripeToken = $UserData[0]['vStripeToken'];
    $userCurrencyCode = $UserData[0]['vCurrencyPassenger'];
    /*$currencyCode = get_value('currency', 'vName', 'eDefault', 'Yes','','true');
    $currencyratio = get_value('currency', 'Ratio', 'vName', $userCurrencyCode,'','true');*/
    $DefaultCurrencyData = get_value('currency', 'vName,Ratio', 'eDefault', 'Yes');
    $currencyCode = $DefaultCurrencyData[0]['vName'];
    $currencyratio = $DefaultCurrencyData[0]['Ratio'];
    //$price = $fAmount*$currencyratio;
    $price = $fAmount / $currencyratio;
    $price_new = $price * 100;
    $price_new = round($price_new);
    if ($vStripeCusId == "" || $vStripeToken == "") {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
        echo json_encode($returnArr);exit;
    }

    $dDate = Date('Y-m-d H:i:s');
    $eFor = 'Deposit';
    $eType = 'Credit';
    $tDescription = '#LBL_AMOUNT_DEBIT#';
    //$tDescription = "Amount debited";
    $ePaymentStatus = 'Unsettelled';

    $userAvailableBalance = $generalobj->get_user_available_balance($iMemberId, $eUserType);
    if ($userAvailableBalance > $price) {
        $where = " iTripId = '$iTripId'";
        $data['fTipPrice'] = $price;

        $id = $obj->MySQLQueryPerform("trips", $data, 'update', $where);

        $vRideNo = get_value('trips', 'vRideNo', 'iTripId', $tripId, '', 'true');
        $data_wallet['iUserId'] = $iUserId;
        $data_wallet['eUserType'] = "Rider";
        $data_wallet['iBalance'] = $price;
        $data_wallet['eType'] = "Debit";
        $data_wallet['dDate'] = date("Y-m-d H:i:s");
        $data_wallet['iTripId'] = $iTripId;
        $data_wallet['eFor'] = "Booking";
        $data_wallet['ePaymentStatus'] = "Unsettelled";
        $data_wallet['tDescription'] = '#LBL_DEBITED_BOOKING#' . $vRideNo;
        //$data_wallet['tDescription']="Debited for trip#".$vRideNo;

        $generalobj->InsertIntoUserWallet($data_wallet['iUserId'], $data_wallet['eUserType'], $data_wallet['iBalance'], $data_wallet['eType'], $data_wallet['iTripId'], $data_wallet['eFor'], $data_wallet['tDescription'], $data_wallet['ePaymentStatus'], $data_wallet['dDate']);

        $returnArr["Action"] = "1";
        echo json_encode($returnArr);exit;

    } else if ($price > 0.51) {
        try {
            $charge_create = Stripe_Charge::create(array(
                "amount" => $price_new,
                "currency" => $currencyCode,
                "customer" => $vStripeCusId,
                "description" => $tDescription,
            ));

            $details = json_decode($charge_create);
            $result = get_object_vars($details);
            //echo "<pre>";print_r($result);exit;
            if ($result['status'] == "succeeded" && $result['paid'] == "1") {

                $where = " iTripId = '$iTripId'";
                $data['fTipPrice'] = $price;

                $id = $obj->MySQLQueryPerform("trips", $data, 'update', $where);

                $returnArr["Action"] = "1";
                echo json_encode($returnArr);exit;
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_TRANS_FAILED";

                echo json_encode($returnArr);exit;
            }

        } catch (Exception $e) {
            //echo "<pre>";print_r($e);exit;
            $error3 = $e->getMessage();
            $returnArr["Action"] = "0";
            $returnArr['message'] = $error3;
            //$returnArr['message']="LBL_TRANS_FAILED";

            echo json_encode($returnArr);exit;
        }

    } else {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_REQUIRED_MINIMUM_AMOUT";
        $returnArr['minValue'] = strval(round(51 * $currencyratio));

        echo json_encode($returnArr);exit;
    }


?>