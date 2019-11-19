<?php 
    global $generalobj, $tconfig;
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $iDriverVehicleId = isset($_REQUEST['iDriverVehicleId']) ? clean($_REQUEST['iDriverVehicleId']) : '';
    //$doc_userid = isset($_REQUEST['doc_userid']) ? clean($_REQUEST['doc_userid']) : '';
    $memberType = isset($_REQUEST['MemberType']) ? clean($_REQUEST['MemberType']) : 'Driver';
    $doc_usertype = isset($_REQUEST['doc_usertype']) ? clean(strtolower($_REQUEST['doc_usertype'])) : 'driver'; // vehicle OR driver
    $doc_masterid = isset($_REQUEST['doc_masterid']) ? clean($_REQUEST['doc_masterid']) : '';
    $doc_name = isset($_REQUEST['doc_name']) ? clean($_REQUEST['doc_name']) : '';
    $doc_id = isset($_REQUEST['doc_id']) ? clean($_REQUEST['doc_id']) : '';
    $doc_file = isset($_REQUEST['doc_file']) ? clean($_REQUEST['doc_file']) : '';
    $ex_date = isset($_REQUEST['ex_date']) ? clean($_REQUEST['ex_date']) : '';
    $ex_status = isset($_REQUEST['ex_status']) ? clean($_REQUEST['ex_status']) : '';
    if ($doc_usertype == "vehicle") {
        $doc_usertype = "car";
    }
    $doc_userid = ($doc_usertype == 'car') ? $iDriverVehicleId : $iMemberId;
    $status = ($doc_usertype == 'car' || $doc_usertype == 'driver') ? "Active" : "Inactive";
    $image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
    //$image_name = "123.jpg";
    $action = ($doc_id != '') ? 'Edit' : 'Add';
    $addupdatemode = ($action == 'Add') ? 'insert' : 'update';

    if ($doc_file != "") {
        $vImageName = $doc_file;
    } else {
        if ($doc_usertype == "driver") {
            $Photo_Gallery_folder = $tconfig['tsite_upload_driver_doc_path'] . "/" . $iMemberId . "/";
        } else {
            $Photo_Gallery_folder = $tconfig['tsite_upload_vehicle_doc'] . "/" . $iDriverVehicleId . "/";
        }
        if (!is_dir($Photo_Gallery_folder)) {
            mkdir($Photo_Gallery_folder, 0777);
        }
        $vFile = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = "pdf,doc,docx,jpg,jpeg,gif,png,xls,xlsx,csv");
        $vImageName = $vFile[0];
    }

    if ($vImageName != '') {
        $Data_Update["doc_masterid"] = $doc_masterid;
        $Data_Update["doc_usertype"] = $doc_usertype;
        $Data_Update["doc_userid"] = $doc_userid;
        $Data_Update["ex_date"] = $ex_date;
        $Data_Update["doc_file"] = $vImageName;
        $Data_Update["edate"] = @date("Y-m-d H:i:s");
        if ($action == "Add") {
            $Data_Update["status"] = $status;
            $id = $obj->MySQLQueryPerform("document_list", $Data_Update, 'insert');
        } else {
            $where = " doc_id = '" . $doc_id . "'";
            $id = $obj->MySQLQueryPerform("document_list", $Data_Update, 'update', $where);
        }
        $generalobj->save_log_data('0', $iMemberId, 'driver', $doc_name, $vImageName);
        if ($id > 0) {
            $returnArr['Action'] = "1";
            //$returnArr['message'] = getDriverDetailInfo($iMemberId);
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);
    exit;

?>