<?php 
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';

    if ($iUserId != "") {
        $vLanguage = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');

        $vLanguage = $vLanguage == "" ? "EN" : $vLanguage;

        $petTypes = get_value('pet_type', 'iPetTypeId, vTitle_' . $vLanguage . ' as vTitle', 'eStatus', 'Active');

        $returnData['Action'] = "1";
        $returnData['message'] = $petTypes;
    } else {
        $returnData['Action'] = "0";
    }
    echo json_encode($returnData);
?>