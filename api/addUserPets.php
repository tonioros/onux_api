<?php 
    global $generalobj;

    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '0';
    $iPetTypeId = isset($_REQUEST["iPetTypeId"]) ? $_REQUEST["iPetTypeId"] : '0';
    $vTitle = isset($_REQUEST["vTitle"]) ? $_REQUEST["vTitle"] : '';
    $vWeight = isset($_REQUEST["vWeight"]) ? $_REQUEST["vWeight"] : '';
    $tBreed = isset($_REQUEST["tBreed"]) ? $_REQUEST["tBreed"] : '';
    $tDescription = isset($_REQUEST["tDescription"]) ? $_REQUEST["tDescription"] : '';

    $Data_pets['iUserId'] = $iUserId;
    $Data_pets['iPetTypeId'] = $iPetTypeId;
    $Data_pets['vTitle'] = $vTitle;
    $Data_pets['vWeight'] = $vWeight;
    $Data_pets['tBreed'] = $tBreed;
    $Data_pets['tDescription'] = $tDescription;

    $id = $obj->MySQLQueryPerform("user_pets", $Data_pets, 'insert');

    if ($id > 0) {
        $returnArr['Action'] = "1";

    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    echo json_encode($returnArr);

?>