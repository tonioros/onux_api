<?php 

    global $generalobj, $tconfig;

    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $memberType = isset($_REQUEST['MemberType']) ? clean($_REQUEST['MemberType']) : '';
    $image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
    $image_name = "123.jpg";

    if ($memberType == "Driver") {
        $Photo_Gallery_folder = $tconfig['tsite_upload_images_driver_path'] . "/" . $iMemberId . "/";
    } else {
        $Photo_Gallery_folder = $tconfig['tsite_upload_images_passenger_path'] . "/" . $iMemberId . "/";
    }

    // echo $Photo_Gallery_folder."===";
    if (!is_dir($Photo_Gallery_folder)) {
        mkdir($Photo_Gallery_folder, 0777);
    }

    // echo $tconfig["tsite_upload_images_member_size1"];exit;

    $vImageName = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], '', '', '', 'Y', '', $Photo_Gallery_folder);

    if ($vImageName != '') {
        if ($memberType == "Driver") {
            $OldImageName = get_value('register_driver', 'vImage', 'iDriverId', $iMemberId, '', 'true');
            $where = " iDriverId = '" . $iMemberId . "'";
            $Data_passenger['vImage'] = $vImageName;
            $id = $obj->MySQLQueryPerform("register_driver", $Data_passenger, 'update', $where);
        } else {
            $OldImageName = get_value('register_user', 'vImgName', 'iUserId', $iMemberId, '', 'true');
            $where = " iUserId = '" . $iMemberId . "'";
            $Data_passenger['vImgName'] = $vImageName;
            $id = $obj->MySQLQueryPerform("register_user", $Data_passenger, 'update', $where);
        }

        unlink($Photo_Gallery_folder . $OldImageName);
        unlink($Photo_Gallery_folder . "1_" . $OldImageName);
        unlink($Photo_Gallery_folder . "2_" . $OldImageName);
        unlink($Photo_Gallery_folder . "3_" . $OldImageName);

        if ($id > 0) {
            $returnArr['Action'] = "1";
            if ($memberType == "Driver") {
                $returnArr['message'] = getDriverDetailInfo($iMemberId);
            } else {
                $returnArr['message'] = getPassengerDetailInfo($iMemberId, "");
            }

        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }

    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    echo json_encode($returnArr);



?>