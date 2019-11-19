<?php 

    $iPageId = isset($_REQUEST['iPageId']) ? clean($_REQUEST['iPageId']) : '';
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $vLangCode = isset($_REQUEST['vLangCode']) ? clean($_REQUEST['vLangCode']) : '';
    $appType = isset($_REQUEST['appType']) ? clean($_REQUEST['appType']) : ''; // Passenger OR Driver

    $languageCode = "";
    if ($iMemberId != "") {
        if ($appType == "Driver") {
            $languageCode = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');
        } else {
            $languageCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
        }
    } else if ($vLangCode != null && $vLangCode != "") {
        $check_lng = get_value('language_master', 'vTitle', 'vCode', $vLangCode, '', 'true');
        if ($check_lng != null) {
            $languageCode = $vLangCode;
        }
    }

    if ($languageCode == "") {
        $languageCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $pageDesc = get_value('pages', 'tPageDesc_' . $languageCode, 'iPageId', $iPageId, '', 'true');
    // $meta['page_desc']=strip_tags($pageDesc);
    $meta['page_desc'] = $pageDesc;
    echo json_encode($meta, JSON_UNESCAPED_UNICODE);
