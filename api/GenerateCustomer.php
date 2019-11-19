<?php 
    $Data = array();
    $Data = $_REQUEST;
    $returnArr = GenerateCustomer($Data);
    ###############################    Stripe Request Param  #####################################
    /*$iUserId     = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';  //Passenger,Driver
    $vStripeToken     = isset($_REQUEST["vStripeToken"]) ? $_REQUEST["vStripeToken"] : '';
    $CardNo     = isset($_REQUEST["CardNo"]) ? $_REQUEST["CardNo"] : '';*/
    ###############################    Stripe Request Param  #####################################
    ###############################    Braintree Request Param  #####################################
    /*$iUserId     = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';  //Passenger,Driver
    $CardNo     = isset($_REQUEST["CardNo"]) ? $_REQUEST["CardNo"] : '';
    $paymentMethodNonce = isset($_REQUEST["paymentMethodNonce"]) ? $_REQUEST["paymentMethodNonce"] : '';*/
    ###############################    Braintree Request Param  #####################################
    ###############################    Paymaya Request Param  #####################################
    /*$iUserId     = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';  //Passenger,Driver
    $vPaymayaToken     = isset($_REQUEST["vPaymayaToken"]) ? $_REQUEST["vPaymayaToken"] : '';
    $CardNo     = isset($_REQUEST["CardNo"]) ? $_REQUEST["CardNo"] : '';*/
    ###############################    Paymaya Request Param  #####################################
    ###############################    Omise Request Param  #####################################
    /*$iUserId     = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';  //Passenger,Driver
    $vOmiseToken     = isset($_REQUEST["vOmiseToken"]) ? $_REQUEST["vOmiseToken"] : '';
    $CardNo     = isset($_REQUEST["CardNo"]) ? $_REQUEST["CardNo"] : '';*/
    ###############################    Omise Request Param  #####################################
    ###############################    Xendit Request Param  #####################################
    /*$iUserId     = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';  //Passenger,Driver
    $vXenditToken     = isset($_REQUEST["vXenditToken"]) ? $_REQUEST["vXenditToken"] : '';    */
    ###############################    Xendit Request Param  #####################################
    echo json_encode($returnArr);exit;

?>