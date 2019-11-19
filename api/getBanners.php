<?php 
    global $generalobj;

    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    if ($iMemberId != "") {

        $vLanguage = get_value('register_user', 'vLang', 'iDriverId', $iDriverId, '', 'true');
        if ($vLanguage == "" || $vLanguage == null) {
            $vLanguage = "EN";
        }

        $banners = get_value('banners', 'vImage', 'vCode', $vLanguage, ' ORDER BY iDisplayOrder ASC');

        $data = array();
        $count = 0;
        for ($i = 0; $i < count($banners); $i++) {
            if ($banners[$i]['vImage'] != "") {
                $row[0][$vName] = ($$vName != "") ? ($tconfig["tsite_url"] . "resizeImg.php?src=" . $tconfig['tsite_upload_images_vehicle_category_path'] . "/" . $$vName . "&h=" . $_REQUEST['HeightOfBanner'] . "&w=" . $_REQUEST['WidthOfBanner']) : "";
                if (isset($_REQUEST['WidthOfBanner']) && isset($_REQUEST['HeightOfBanner'])) {
                    $data[$count]['vImage'] = $tconfig["tsite_url"] . "resizeImg.php?src=" . $tconfig["tpanel_path"] . "assets/img/images/" . $banners[$i]['vImage'] . "&h=" . $_REQUEST['HeightOfBanner'] . "&w=" . $_REQUEST['WidthOfBanner'];
                } else {
                    $data[$count]['vImage'] = $tconfig["tsite_url"] . 'assets/img/images/' . $banners[$i]['vImage'];
                }
                $count++;
            }
        }

        $returnArr['Action'] = "1";
        $returnArr['message'] = $data;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    echo json_encode($returnArr);

?>