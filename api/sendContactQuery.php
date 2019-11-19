<?php 

    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
    $UserId = isset($_REQUEST["UserId"]) ? $_REQUEST["UserId"] : '';
    $message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : '';
    $subject = isset($_REQUEST["subject"]) ? $_REQUEST["subject"] : '';

    if ($UserType == 'Passenger') {
        $sql = "SELECT vName,vLastName,vPhone,vEmail FROM register_user WHERE iUserId=$UserId";

        $result_data = $obj->MySQLSelect($sql);

    } else if ($UserType == 'Driver') {
        $sql = "SELECT vName,vLastName,vPhone,vEmail FROM register_driver WHERE iDriverId=$UserId";

        $result_data = $obj->MySQLSelect($sql);

    }

    if ($UserId != "") {
        $Data['vFirstName'] = $result_data[0]['vName'];
        $Data['vLastName'] = $result_data[0]['vLastName'];
        $Data['vEmail'] = $result_data[0]['vEmail'];
        $Data['cellno'] = $result_data[0]['vPhone'];
    } else {
        $Data['vFirstName'] = "App User";
        $Data['vLastName'] = "";
        $Data['vEmail'] = "-";
        $Data['cellno'] = "-";
    }
    $Data['eSubject'] = $subject;
    $Data['tSubject'] = $message;
    $id = $generalobj->send_email_user("CONTACTUS", $Data);

    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_SENT_CONTACT_QUERY_SUCCESS_TXT";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_FAILED_SEND_CONTACT_QUERY_TXT";
    }
    echo json_encode($returnArr);
