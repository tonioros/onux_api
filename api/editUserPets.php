<?php 
    $iUserPetId = isset($_REQUEST["iUserPetId"]) ? $_REQUEST['iUserPetId'] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST['iUserId'] : '';
    $iPetTypeId = isset($_REQUEST["iPetTypeId"]) ? $_REQUEST['iPetTypeId'] : '';
    $vTitle = isset($_REQUEST["vTitle"]) ? $_REQUEST['vTitle'] : '';
    $vWeight = isset($_REQUEST["vWeight"]) ? $_REQUEST['vWeight'] : '';
    $tBreed = isset($_REQUEST["tBreed"]) ? $_REQUEST['tBreed'] : '';
    $tDescription = isset($_REQUEST["tDescription"]) ? $_REQUEST['tDescription'] : '';

    $where = " iUserPetId = '" . $iUserPetId . "' AND `iUserId`='" . $iUserId . "'";

    $Data['iUserId'] = $iUserId;
    $Data['iPetTypeId'] = $iPetTypeId;
    $Data['vTitle'] = $vTitle;
    $Data['vWeight'] = $vWeight;
    $Data['tBreed'] = $tBreed;
    $Data['tDescription'] = $tDescription;
    $id = $obj->MySQLQueryPerform("user_pets", $Data, 'update', $where);

    if ($id) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_INFO_UPDATED_TXT";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    echo json_encode($returnArr);

?>