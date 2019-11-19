<?php 

    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iCabRequestId = isset($_REQUEST["iCabRequestId"]) ? $_REQUEST["iCabRequestId"] : '';

    if ($iCabRequestId == "") {
        // $data = get_value('cab_request_now', 'max(iCabRequestId),eStatus', 'iUserId',$iUserId);
        $sql = "SELECT iCabRequestId, eStatus FROM cab_request_now WHERE iUserId='" . $iUserId . "' ORDER BY iCabRequestId DESC LIMIT 1 ";
        $data = $obj->MySQLSelect($sql);
        $iCabRequestId = $data[0]['iCabRequestId'];
        $eStatus = $data[0]['eStatus'];
    } else {
        $data = get_value('cab_request_now', 'eStatus', 'iCabRequestId', $iCabRequestId, '', 'true');
        $eStatus = $data[0]['eStatus'];
    }
    if ($eStatus == "Requesting") {
        $where = " iCabRequestId='$iCabRequestId'";
        $Data_update_cab_request['eStatus'] = "Cancelled";

        $id = $obj->MySQLQueryPerform("cab_request_now", $Data_update_cab_request, 'update', $where);

        if ($id) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "DO_RESET";
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_REQUEST_CANCEL_FAILED_TXT";
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "DO_RESTART";
    }

    echo json_encode($returnArr);

?>