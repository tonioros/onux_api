<?php 
    $vLang = isset($_REQUEST['vLang']) ? clean($_REQUEST['vLang']) : '';
    $UpdatedLanguageLabels = getLanguageLabelsArr($vLang, "1");

    $lngData = get_value('language_master', 'vCode, vGMapLangCode, eDirectionCode as eType, vTitle', 'vCode', $vLang);

    $returnArr['Action'] = "1";
    $returnArr['message'] = $UpdatedLanguageLabels;
    $returnArr['vCode'] = $lngData[0]['vCode'];
    $returnArr['vGMapLangCode'] = $lngData[0]['vGMapLangCode'];
    $returnArr['eType'] = $lngData[0]['eType'];
    $returnArr['vTitle'] = $lngData[0]['vTitle'];

    $obj->MySQLClose();
    echo json_encode($returnArr);
    exit;

?>