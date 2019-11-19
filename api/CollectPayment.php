<?php 
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $isCollectCash = isset($_REQUEST["isCollectCash"]) ? $_REQUEST["isCollectCash"] : '';

    $sql = "SELECT vTripPaymentMode,iUserId,iDriverId,iFare,vRideNo,fWalletDebit,fTripGenerateFare,fDiscount,fCommision,fTollPrice,eHailTrip,fOutStandingAmount,iActive,ePaymentCollect FROM trips WHERE iTripId='$iTripId'";
    $tripData = $obj->MySQLSelect($sql);

    $vTripPaymentMode = $tripData[0]['vTripPaymentMode'];
    $data['vTripPaymentMode'] = $vTripPaymentMode;
    $iUserId = $tripData[0]['iUserId'];
    //$iFare = $tripData[0]['iFare']+$tripData[0]['fTollPrice'];
    $iFare = $tripData[0]['iFare'];
    $vRideNo = $tripData[0]['vRideNo'];
    $eHailTrip = $tripData[0]['eHailTrip'];
    $ePaymentCollect = $tripData[0]['ePaymentCollect'];
    $driverId = $tripData[0]['iDriverId'];
    $estimatefare = 0;
    $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    $estimatefare = get_value('estimatefare', 'Estimate_Fare', 'User_Id', $iUserId, '', 'true');
    $i = 0;
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
    $eDeviceType = get_value('register_driver', 'eDeviceType', 'iDriverId', $driverId, '', 'true');
    if ($eDeviceType == "Ios") {
        //$txt=$ePaymentCollect."|".$iFare."|".$estimatefare."|".$isCollectCash;
        //$myfile = file_put_contents('example1.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
        $ePaymentCollect = "Yes";
    }

    if ($ePaymentCollect == "Yes") {
        // $myfile = fopen("example.txt", "w") or die("Unable to open file!");
        // fwrite($myfile, "check for payment collec yes \n");
        if ($iFare > $estimatefare) {
            $iFare = $iFare - $estimatefare;
            $i = 1;
            //  fwrite($myfile, "total is greater \n");
        } else if ($iFare < $estimatefare) {
            //  fwrite($myfile, "total fare is less \n");
            $iFare = $estimatefare - $iFare;

            $i = 2;
        }
        if ($vTripPaymentMode == "Card" && $isCollectCash == "" && $i > 0) {
            //    fwrite($myfile, "enter into payment condition \n");
            $vStripeCusId = get_value('register_user', 'vStripeCusId', 'iUserId', $iUserId, '', 'true');
            $vBrainTreeToken = get_value('register_user', 'vBrainTreeToken', 'iUserId', $iUserId, '', 'true');
            $price_new = $iFare * 100;

            $currency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');

            $description = $languageLabelsArr['LBL_TRIP_PAYMENT_RECEIVED'] . " " . $vRideNo;

            if ($i == 1 && $iFare >= 300) {

                //    $price_new = $iFare * 100;
                $Charge_Array = array("iFare" => $iFare, "price_new" => $price_new, "currency" => $currency, "vStripeCusId" => $vStripeCusId, "description" => $description, "iTripId" => $iTripId, "eCancelChargeFailed" => "No", "vBrainTreeToken" => $vBrainTreeToken, "vRideNo" => $vRideNo, "iMemberId" => $iUserId, "UserType" => "Passenger");
                $ChargeidArr = ChargeCustomer($Charge_Array, "CollectPayment"); // function for charge customer
                $ChargeidArrId = $ChargeidArr['id'];
                $status = $ChargeidArr['status'];
                // fwrite($myfile, "charge \n");
            } else if ($i == 2) {
                //  fwrite($myfile, "refund \n");
                $iFare = $iFare * 100;
                // fwrite($myfile, $iFare);
                $sql = "SELECT tPaymentUserID FROM payments WHERE iTripId='$iTripId'";
                $chargeid = $obj->MySQLSelect($sql);
                //    $chargeid = get_value('payments', 'tPaymentUserID', 'iTripId', $iTripId,'','true');
                // fwrite($myfile,print_r($chargeid,true));
                try { $ch = Stripe_Charge::retrieve($chargeid[0]['tPaymentUserID']);
                    $ch->refund([
                        'amount' => $iFare,
                    ]);

                } catch (Exception $e) {
                    // fwrite($myfile, $e->getMessage());
                }

            }

            $data['vTripPaymentMode'] = "Card";
        } else if ($vTripPaymentMode == "Card" && $isCollectCash == "true") {
            // echo "else if";exit;
            $data['vTripPaymentMode'] = "Cash";
            //    fclose($myfile);
        }
    } else {

        if ($vTripPaymentMode == "Card" && $isCollectCash == "") {

            $vStripeCusId = get_value('register_user', 'vStripeCusId', 'iUserId', $iUserId, '', 'true');
            $vBrainTreeToken = get_value('register_user', 'vBrainTreeToken', 'iUserId', $iUserId, '', 'true');

            $price_new = $iFare * 100;
            $currency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');

            $description = $languageLabelsArr['LBL_TRIP_PAYMENT_RECEIVED'] . " " . $vRideNo;
            $Charge_Array = array("iFare" => $iFare, "price_new" => $price_new, "currency" => $currency, "vStripeCusId" => $vStripeCusId, "description" => $description, "iTripId" => $iTripId, "eCancelChargeFailed" => "No", "vBrainTreeToken" => $vBrainTreeToken, "vRideNo" => $vRideNo, "iMemberId" => $iUserId, "UserType" => "Passenger");
            $ChargeidArr = ChargeCustomer($Charge_Array, "CollectPayment"); // function for charge customer
            $ChargeidArrId = $ChargeidArr['id'];
            $status = $ChargeidArr['status'];

            $data['vTripPaymentMode'] = "Card";
        } else if ($vTripPaymentMode == "Card" && $isCollectCash == "true") {
            // echo "else if";exit;
            $data['vTripPaymentMode'] = "Cash";
        }

    }

    // echo "out";exit;
    $where = " iTripId = '$iTripId'";
    $data['ePaymentCollect'] = "Yes";

    $id = $obj->MySQLQueryPerform("trips", $data, 'update', $where);

    $fWalletDebit = $tripData[0]['fWalletDebit'];
    $fDiscount = $tripData[0]['fDiscount'];
    $discountValue = $fWalletDebit + $fDiscount;
    //$discountValue = $tripData[0]['fDiscount'];
    //$walletamountofcreditcard = $tripData[0]['fTripGenerateFare']+$tripData[0]['fTollPrice'];
    $walletamountofcreditcard = $tripData[0]['fTripGenerateFare'];

    //$COMMISION_DEDUCT_ENABLE=$generalobj->getConfigurations("configurations","COMMISION_DEDUCT_ENABLE");
    if ($COMMISION_DEDUCT_ENABLE == 'Yes') {
        #Deduct Amount From Driver's Wallet Acount#
        $vTripPaymentMode = $data['vTripPaymentMode'];
        if ($vTripPaymentMode == "Cash") {
            $vRideNo = $tripData[0]['vRideNo'];
            //$iBalance = $tripData[0]['fCommision'];
            $iBalance = $tripData[0]['fCommision'] + $tripData[0]['fOutStandingAmount'];
            $eFor = "Withdrawl";
            $eType = "Debit";
            $iTripId = $iTripId;
            //$tDescription = 'Debited for booking#'.$vRideNo;
            $tDescription = '#LBL_DEBITED_BOOKING# ' . $vRideNo;
            $ePaymentStatus = 'Settelled';
            $dDate = Date('Y-m-d H:i:s');
            if ($discountValue > 0) {
                $eFor_credit = "Deposit";
                $eType_credit = "Credit";
                $tDescription_credit = '#LBL_CREDITED_BOOKING# ' . $vRideNo;
                //$tDescription_credit = 'Credited for booking#'.$vRideNo;
                $generalobj->InsertIntoUserWallet($driverId, "Driver", $discountValue, $eType_credit, $iTripId, $eFor_credit, $tDescription_credit, $ePaymentStatus, $dDate);
            }
            if ($iBalance > 0) {
                $generalobj->InsertIntoUserWallet($driverId, "Driver", $iBalance, $eType, $iTripId, $eFor, $tDescription, $ePaymentStatus, $dDate);
            }
            $Where = " iTripId = '$iTripId'";
            $Data_update_driver_paymentstatus['eDriverPaymentStatus'] = "Unsettelled";
            $Update_Payment_Id = $obj->MySQLQueryPerform("trips", $Data_update_driver_paymentstatus, 'update', $Where);
        }
        /* else{
        $vRideNo = $tripData[0]['vRideNo'];
        $iBalance = $walletamountofcreditcard-$tripData[0]['fCommision'];
        $eFor = "Deposit";
        $eType = "Credit";
        $iTripId = $iTripId;
        $tDescription = ' Amount '.$iBalance.' Credited into your account for booking no#'.$vRideNo;
        $ePaymentStatus = 'Settelled';
        $dDate =   Date('Y-m-d H:i:s');
        $generalobj->InsertIntoUserWallet($driverId,"Driver",$iBalance,$eType,$iTripId,$eFor,$tDescription,$ePaymentStatus,$dDate);
        $Where = " iTripId = '$iTripId'";
        $Data_update_driver_paymentstatus['eDriverPaymentStatus']="Settelled";
        $Update_Payment_Id = $obj->MySQLQueryPerform("trips",$Data_update_driver_paymentstatus,'update',$Where);
        } */
        #Deduct Amount From Driver's Wallet Acount#
    }
    if ($id > 0) {
        $trippayment = get_value('trips', 'vTripPaymentMode', 'iTripId', $iTripId, '', 'true');
        $walletdabet = get_value('trips', 'fWalletDebit', 'iTripId', $iTripId, '', 'true');
        $txt = $walletdabet . " |" . $tripData[0]['iActive'] . " |" . $trippayment;
        $myfile = file_put_contents('example.txt', $txt . PHP_EOL, FILE_APPEND | LOCK_EX);
        if ($tripData[0]['iActive'] == "Finished" && $trippayment == "Card") {

            $driverfare = $tripData[0]['fTripGenerateFare'] * 0.1;
            $driverfare = $tripData[0]['fTripGenerateFare'] - $driverfare;

            $driver_date = @date("Y-m-d H:i:s");

            $generalobj->InsertIntoUserWallet($driverId, "Driver", $driverfare, "Credit", $iTripId, "Deposit", "#LBL_CREDITED_BOOKING#" . $tripData[0]['vRideNo'], "Unsettelled", $driver_date);

        } else if ($tripData[0]['iActive'] == "Finished" && $trippayment == "Cash") {
            $txt = "In the cash section";
            $myfile = file_put_contents('example.txt', $txt . PHP_EOL, FILE_APPEND | LOCK_EX);
            if ($walletdabet > 0) {
                $txt = "In wallet section";
                $myfile = file_put_contents('example.txt', $txt . PHP_EOL, FILE_APPEND | LOCK_EX);

                $driver_date = @date("Y-m-d H:i:s");

                $generalobj->InsertIntoUserWallet($driverId, "Driver", $walletdabet, "CREDIT", $iTripId, "Deposit", "#LBL_CREDITED_BOOKING#" . $tripData[0]['vRideNo'], "Unsettelled", $driver_date);
            } /*else{
        $driverfare=$Fare_data['fTripGenerateFare']*0.9;
        $driverfare=$Fare_data['fTripGenerateFare']-$driverfare;

        $driver_date = @date("Y-m-d H:i:s");

        $generalobj->InsertIntoUserWallet($driverId, "Driver", $driverfare,"DEBIT",$tripId, "Charges", "#LBL_DEBITED_BOOKING#".$result22[0]['vRideNo'], "Unsettelled", $driver_date);
        }*/
        }
        $returnArr['Action'] = "1";
        /*$updateQuery = "UPDATE register_user set fTripsOutStandingAmount = '0' WHERE iUserId = ".$iUserId;
        $obj->sql_query($updateQuery);

        //$updateQury = "UPDATE trip_outstanding_amount set ePaidByPassenger = 'Yes' WHERE iUserId = ".$iUserId;
        $updateQury = "UPDATE trip_outstanding_amount set ePaidByPassenger = 'Yes',vTripAdjusmentId = '".$iTripId."' WHERE iUserId = '".$iUserId."' AND ePaidByPassenger = 'No'";
        $obj->sql_query($updateQury);*/
        // Rating entry if trip is hail
        if ($eHailTrip == "Yes") {

            $Data_update_ratings['iTripId'] = $iTripId;
            $Data_update_ratings['vRating1'] = "0.0";
            $Data_update_ratings['vMessage'] = "";
            $Data_update_ratings['eUserType'] = "Driver";

            $obj->MySQLQueryPerform("ratings_user_driver", $Data_update_ratings, 'insert');

            $Data_update_ratings['eUserType'] = "Passenger";

            $obj->MySQLQueryPerform("ratings_user_driver", $Data_update_ratings, 'insert');
            sendTripReceiptAdmin($iTripId);
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    echo json_encode($returnArr);

?>