<?php 

    $configurations = array();
    $configurations["LBL_PAYMENT_ENABLED"] = $PAYMENT_ENABLED;
    $configurations["LBL_BASE_FARE"] = $BASE_FARE;
    $configurations["LBL_FARE_PER_MINUTE"] = $FARE_PER_MINUTE;
    $configurations["LBL_FARE_PAR_KM"] = $FARE_PAR_KM;
    $configurations["LBL_SERVICE_TAX"] = $SERVICE_TAX;

    echo json_encode($configurations);
