<?php 
    $tripId = isset($_REQUEST["TripId"]) ? $_REQUEST["TripId"] : '';
    $PayPalPaymentId = isset($_REQUEST["PayPalPaymentId"]) ? $_REQUEST["PayPalPaymentId"] : '';
    $PaidAmount = isset($_REQUEST["PaidAmount"]) ? $_REQUEST["PaidAmount"] : '';

    $Data_payments['tPaymentUserID'] = $PayPalPaymentId;
    $Data_payments['vPaymentUserStatus'] = "approved";
    $Data_payments['iTripId'] = $tripId;
    $Data_payments['iAmountUser'] = $PaidAmount;

    $id = $obj->MySQLQueryPerform("payments", $Data_payments, 'insert');
    if ($id > 0) {
        echo "PaymentSuccessful";
    } else {
        echo "PaymentUnSuccessful";
    }

?>