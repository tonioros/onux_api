<?php 
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;

    $vLanguage = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');

    $vLanguage = $vLanguage == "" ? "EN" : $vLanguage;

    $per_page = 10;
    $sql = "SELECT COUNT(iUserPetId) as TotalIds from user_pets WHERE iUserId='" . $iUserId . "'";

    $Data_all = $obj->MySQLSelect($sql);
    $TotalPages = ceil($Data_all[0]['TotalIds'] / $per_page);

    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;

    $sql = "SELECT up.*,pt.vTitle_" . $vLanguage . " as petType from user_pets as up,  pet_type as pt WHERE pt.iPetTypeId = up.iPetTypeId AND up.iUserId='" . $iUserId . "'" . $limit;
    $Data = $obj->MySQLSelect($sql);

    $totalNum = count($Data);

    if (count($Data) > 0 && $iUserId != "") {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $Data;
        if ($TotalPages > $page) {
            $returnArr['NextPage'] = $page + 1;
        } else {
            $returnArr['NextPage'] = "0";
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    echo json_encode($returnArr);

?>