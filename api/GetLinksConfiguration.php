<?php 
    $UserType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : '';

    if ($UserType == 'Passenger') {
        $DataArr['LINK_FORGET_PASS_PAGE_PASSENGER'] = $tconfig["tsite_url"] . $LINK_FORGET_PASS_PAGE_PASSENGER;
        $DataArr['FACEBOOK_APP_ID'] = $FACEBOOK_APP_ID;
        $DataArr['CONFIG_CLIENT_ID'] = $CONFIG_CLIENT_ID;
        $DataArr['GOOGLE_SENDER_ID'] = $GOOGLE_SENDER_ID;
        $DataArr['MOBILE_VERIFICATION_ENABLE'] = $MOBILE_VERIFICATION_ENABLE;

        echo json_encode($DataArr);
    } else if ($UserType == 'Driver') {
        $DataArr['LINK_FORGET_PASS_PAGE_DRIVER'] = $tconfig["tsite_url"] . $LINK_FORGET_PASS_PAGE_DRIVER;
        $DataArr['LINK_SIGN_UP_PAGE_DRIVER'] = $tconfig["tsite_url"] . $LINK_SIGN_UP_PAGE_DRIVER;
        $DataArr['GOOGLE_SENDER_ID'] = $GOOGLE_SENDER_ID;
        $DataArr['MOBILE_VERIFICATION_ENABLE'] = $MOBILE_VERIFICATION_ENABLE;

        echo json_encode($DataArr);
    }

?>