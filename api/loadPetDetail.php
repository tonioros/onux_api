<?php 
    $iUserPetId = isset($_REQUEST["iUserPetId"]) ? $_REQUEST['iUserPetId'] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST['iUserId'] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST['iDriverId'] : '';

    $vLanguage = get_value('register_user', 'vLang', 'iDriverId', $iDriverId, '', 'true');
    if ($vLanguage == "" || $vLanguage == null) {
        $vLanguage = "EN";
    }

    $sql = "SELECT up.*,pt.vTitle_" . $vLanguage . " as petTypeName from user_pets as up,  pet_type as pt WHERE pt.iPetTypeId = up.iPetTypeId AND up.iUserId='" . $iUserId . "' AND up.iUserPetId='" . $iUserPetId . "'";
    $Data = $obj->MySQLSelect($sql);

    if (count($Data) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $Data[0];
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    echo json_encode($returnArr);

?>