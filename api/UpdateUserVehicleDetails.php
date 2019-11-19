<?php 

    global $generalobj, $tconfig;
    $iUserVehicleId = isset($_REQUEST['iUserVehicleId']) ? $_REQUEST['iUserVehicleId'] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iMakeId = isset($_REQUEST["iMakeId"]) ? $_REQUEST["iMakeId"] : '';
    $iModelId = isset($_REQUEST["iModelId"]) ? $_REQUEST["iModelId"] : '';
    $iYear = isset($_REQUEST["iYear"]) ? $_REQUEST["iYear"] : '';
    $vLicencePlate = isset($_REQUEST["vLicencePlate"]) ? $_REQUEST["vLicencePlate"] : '';
    $vColour = isset($_REQUEST["vColour"]) ? $_REQUEST["vColour"] : '';
    $eStatus = isset($_REQUEST["eStatus"]) ? $_REQUEST["eStatus"] : 'Inactive';
    //$vImage = isset($_REQUEST["vImage"]) ? $_REQUEST["vImage"] : '';

    $image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';

    $Photo_Gallery_folder = $tconfig['tsite_upload_images_passenger_vehicle'] . "/" . $iUserVehicleId . "/"; // /webimages/upload/uservehicle

    // echo $Photo_Gallery_folder."===";
    if (!is_dir($Photo_Gallery_folder)) {
        mkdir($Photo_Gallery_folder, 0777);
    }

    $action = ($iUserVehicleId != '') ? 'Edit' : 'Add';

    $Data_User_Vehicle['iUserId'] = $iUserId;
    $Data_User_Vehicle['iMakeId'] = $iMakeId;
    $Data_User_Vehicle['iModelId'] = $iModelId;
    $Data_User_Vehicle['iYear'] = $iYear;
    $Data_User_Vehicle['vLicencePlate'] = $vLicencePlate;
    $Data_User_Vehicle['eStatus'] = $eStatus;
    $Data_User_Vehicle['vColour'] = $vColour;
    //$Data_User_Vehicle['vImage']=$vImage;

    if ($action == "Add") {
        $id = $obj->MySQLQueryPerform("user_vehicle", $Data_User_Vehicle, 'insert');
        $updateimageid = $id;
    } else {
        $where = " iUserVehicleId = '" . $iUserVehicleId . "'";
        $updateimageid = $iUserVehicleId;
        $id = $obj->MySQLQueryPerform("user_vehicle", $Data_User_Vehicle, 'update', $where);
    }

    if ($image_name != "") {
        $vFile = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = "pdf,doc,docx,jpg,jpeg,gif,png");
        $vImageName = $vFile[0];
        $Data_passenger["vImage"] = $vImageName;
        $where_image = " iUserVehicleId = '" . $updateimageid . "'";
        $id = $obj->MySQLQueryPerform("user_vehicle", $Data_passenger, 'update', $where_image);
    }

    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = getPassengerDetailInfo($iUserId);
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);
    exit;

?>