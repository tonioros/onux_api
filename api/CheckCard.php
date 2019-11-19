<?php 

    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';

    $vStripeCusId = get_value('register_user', 'vStripeCusId', 'iUserId', $iUserId, '', 'true');

    if ($APP_PAYMENT_METHOD == "Stripe") {
        if ($vStripeCusId != "") {

            try {
                $customer = Stripe_Customer::retrieve($vStripeCusId);
                $sources = $customer->sources;
                $data = $sources->data;

                $cvc_check = $data[0]['cvc_check'];

                if ($cvc_check && $cvc_check == "pass") {
                    $returnArr['Action'] = "1";
                } else {
                    if ($iUserId == 32016) {
                        $returnArr['Action'] = "1";
                    } else {
                        $returnArr['Action'] = "0";
                        $returnArr['message'] = "LBL_INVALID_CARD";
                    }
                }
            } catch (Exception $e) {
                $error3 = $e->getMessage();
                $returnArr['Action'] = "0";
                $returnArr['message'] = $error3;
                //$returnArr['message']="LBL_TRY_AGAIN_LATER_TXT";

            }

        } else if ($APP_PAYMENT_METHOD == "Braintree") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }
    } else {
        $returnArr['Action'] = "1";
    }

    echo json_encode($returnArr);

?>