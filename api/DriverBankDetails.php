<?php 
    global $generalobj, $obj;

    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Driver';
    $eDisplay = isset($_REQUEST["eDisplay"]) ? $_REQUEST["eDisplay"] : 'Yes';
    $vPaymentEmail = isset($_REQUEST["vPaymentEmail"]) ? $_REQUEST["vPaymentEmail"] : '';
    $vBankAccountHolderName = isset($_REQUEST["vBankAccountHolderName"]) ? $_REQUEST["vBankAccountHolderName"] : '';
    $vAccountNumber = isset($_REQUEST["vAccountNumber"]) ? $_REQUEST["vAccountNumber"] : '';
    $vBankLocation = isset($_REQUEST["vBankLocation"]) ? $_REQUEST["vBankLocation"] : '';
    $vBankName = isset($_REQUEST["vBankName"]) ? $_REQUEST["vBankName"] : '';
    $vBIC_SWIFT_Code = isset($_REQUEST["vBIC_SWIFT_Code"]) ? $_REQUEST["vBIC_SWIFT_Code"] : '';

    if ($eDisplay == "" || $eDisplay == null) {
        $eDisplay = "Yes";
    }
    $returnArr = array();
    if ($eDisplay == "Yes") {
        $Driver_Bank_Arr = get_value('register_driver', 'vPaymentEmail, vBankAccountHolderName, vAccountNumber, vBankLocation, vBankName, vBIC_SWIFT_Code', 'iDriverId', $iDriverId);
        $returnArr['Action'] = "1";
        $returnArr['message'] = $Driver_Bank_Arr[0];
        echo json_encode($returnArr);exit;
    } else {
        $Data_Update['vPaymentEmail'] = $vPaymentEmail;
        $Data_Update['vBankAccountHolderName'] = $vBankAccountHolderName;
        $Data_Update['vAccountNumber'] = $vAccountNumber;
        $Data_Update['vBankLocation'] = $vBankLocation;
        $Data_Update['vBankName'] = $vBankName;
        $Data_Update['vBIC_SWIFT_Code'] = $vBIC_SWIFT_Code;

        $where = " iDriverId = '" . $iDriverId . "'";
        $obj->MySQLQueryPerform("register_driver", $Data_Update, 'update', $where);

        $returnArr['Action'] = "1";
        $returnArr['message'] = getDriverDetailInfo($iDriverId);
        echo json_encode($returnArr);exit;
    }

?>