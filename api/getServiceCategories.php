<?php 

    global $generalobj;

    $parentId = isset($_REQUEST['parentId']) ? clean($_REQUEST['parentId']) : 0;
    $userId = isset($_REQUEST['userId']) ? clean($_REQUEST['userId']) : '';
    if ($userId != "") {
        $sql1 = "SELECT vLang FROM `register_user` WHERE iUserId='$userId'";
        $row = $obj->MySQLSelect($sql1);
        $lang = $row[0]['vLang'];
        if ($lang == "") {$lang = "EN";}

        //$vehicle_category = get_value('vehicle_category', 'iVehicleCategoryId, vLogo,vCategory_'.$row[0]['vLang'].' as vCategory', 'eStatus', 'Active');
        // $sql2 = "SELECT iVehicleCategoryId, vLogo,vCategory_".$lang." as vCategory FROM vehicle_category WHERE eStatus='Active' AND iParentId='$parentId'";
        /*if($parentId == 0){
        $sql2 = "SELECT vc.iVehicleCategoryId, vc.vLogo,vc.vCategory_".$lang." as vCategory FROM vehicle_category as vc WHERE vc.eStatus='Active' AND vc.iParentId='$parentId' and (select count(iVehicleCategoryId) from vehicle_category where iParentId=vc.iVehicleCategoryId) > 0";
        }else{
        $sql2 = "SELECT iVehicleCategoryId, vLogo,vCategory_".$lang." as vCategory FROM vehicle_category WHERE eStatus='Active' AND iParentId='$parentId'";
        }   */
        $sql2 = "SELECT iVehicleCategoryId, vLogo, eShowType,vBannerImage, vCategory_" . $lang . " as vCategory FROM vehicle_category WHERE eStatus='Active' AND iParentId='$parentId' ORDER BY iDisplayOrder ASC";
        $Data = $obj->MySQLSelect($sql2);
        $Datacategory = array();
        if ($parentId == 0) {
            if (count($Data) > 0) {
                $k = 0;
                for ($i = 0; $i < count($Data); $i++) {
                    $sql3 = "SELECT iVehicleCategoryId, vLogo, eShowType,vBannerImage, vCategory_" . $lang . " as vCategory FROM vehicle_category WHERE eStatus='Active' AND iParentId='" . $Data[$i]['iVehicleCategoryId'] . "' ORDER BY iDisplayOrder ASC";
                    $Data2 = $obj->MySQLSelect($sql3);
                    if (count($Data2) > 0) {
                        for ($j = 0; $j < count($Data2); $j++) {
                            $sql4 = "SELECT iVehicleTypeId FROM vehicle_type WHERE iVehicleCategoryId='" . $Data2[$j]['iVehicleCategoryId'] . "'";
                            $Data3 = $obj->MySQLSelect($sql4);
                            if (count($Data3) > 0) {
                                $Datacategory[$k]['iVehicleCategoryId'] = $Data[$i]['iVehicleCategoryId'];
                                $Datacategory[$k]['vLogo'] = $Data[$i]['vLogo'];
                                if (isset($_REQUEST['WidthHeightOfGrid'])) {
                                    $Datacategory[$k]['vLogo_image'] = $tconfig["tsite_url"] . "resizeImg.php?src=" . $tconfig['tsite_upload_images_vehicle_category_path'] . "/" . $Data[$i]['iVehicleCategoryId'] . '/android/' . $Data[$i]['vLogo'] . "&h=" . $_REQUEST['WidthHeightOfGrid'] . "&w=" . $_REQUEST['WidthHeightOfGrid'];
                                } else {
                                    $Datacategory[$k]['vLogo_image'] = $tconfig['tsite_upload_images_vehicle_category'] . '/' . $Data[$i]['iVehicleCategoryId'] . '/android/' . $Data[$i]['vLogo'];
                                }
                                $Datacategory[$k]['vCategory'] = $Data[$i]['vCategory'];
                                $Datacategory[$k]['eShowType'] = $Data[$i]['eShowType'];
                                if (isset($_REQUEST['WidthOfBanner']) && isset($_REQUEST['HeightOfBanner'])) {
                                    $Datacategory[$k]['vBannerImage'] = ($Data[$i]['vBannerImage'] != "" && $Data[$i]['eShowType'] == "Banner") ? ($tconfig["tsite_url"] . "resizeImg.php?src=" . $tconfig['tsite_upload_images_vehicle_category_path'] . '/' . $Data[$i]['iVehicleCategoryId'] . '/' . $Data[$i]['vBannerImage'] . "&h=" . $_REQUEST['HeightOfBanner'] . "&w=" . $_REQUEST['WidthOfBanner']) : "";
                                } else {
                                    $Datacategory[$k]['vBannerImage'] = ($Data[$i]['vBannerImage'] != "" && $Data[$i]['eShowType'] == "Banner") ? $tconfig['tsite_upload_images_vehicle_category'] . '/' . $Data[$i]['iVehicleCategoryId'] . '/' . $Data[$i]['vBannerImage'] : "";
                                }
                                $k++;
                            }
                        }
                        //$Datacategory = array_map('unserialize', array_unique(array_map('serialize', $Datacategory)));
                    }

                }
            }
        } else {
            if (count($Data) > 0) {
                $k = 0;
                for ($j = 0; $j < count($Data); $j++) {
                    $sql4 = "SELECT iVehicleTypeId FROM vehicle_type WHERE iVehicleCategoryId='" . $Data[$j]['iVehicleCategoryId'] . "' ORDER BY iDisplayOrder ASC";
                    $Data3 = $obj->MySQLSelect($sql4);
                    if (count($Data3) > 0) {
                        $Datacategory[$k]['iVehicleCategoryId'] = $Data[$j]['iVehicleCategoryId'];
                        $Datacategory[$k]['vLogo'] = $Data[$j]['vLogo'];
                        $Datacategory[$k]['vLogo_image'] = $tconfig['tsite_upload_images_vehicle_category'] . '/' . $Data[$j]['iVehicleCategoryId'] . '/android/' . $Data[$j]['vLogo'];
                        $Datacategory[$k]['vCategory'] = $Data[$j]['vCategory'];
                        $k++;
                    }
                    //$unique = array_map('unserialize', array_unique(array_map('serialize', $array)));
                }
                //$Datacategory = array_map('unserialize', array_unique(array_map('serialize', $Datacategory)));
            }
        }

        $Datacategory1 = array_unique($Datacategory, SORT_REGULAR);
        $DatanewArr = array();
        foreach ($Datacategory1 as $inner) {
            array_push($DatanewArr, $inner);
        }

        $returnArr['Action'] = "1";
        //$returnArr['message'] = array_reverse($DatanewArr);
        $returnArr['message'] = $DatanewArr;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    echo json_encode($returnArr);

?>