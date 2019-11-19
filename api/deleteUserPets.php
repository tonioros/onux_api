<?php 
    global $generalobj;

    $iUserPetId = isset($_REQUEST["iUserPetId"]) ? $_REQUEST["iUserPetId"] : '0';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '0';

    $sql = "DELETE FROM user_pets WHERE `iUserPetId`='" . $iUserPetId . "' AND `iUserId`='" . $iUserId . "'";
    $id = $obj->sql_query($sql);
    // echo "ID:".$id;exit;
    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_INFO_UPDATED_TXT";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    echo json_encode($returnArr);
?>