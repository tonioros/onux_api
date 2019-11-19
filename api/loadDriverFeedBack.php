<?php 
    global $generalobj, $tconfig;

    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';

    $vAvgRating = get_value('register_driver', 'vAvgRating', 'iDriverId', $iDriverId, '', 'true');

    $per_page = 10;
    $sql_all = "SELECT COUNT(iTripId) As TotalIds FROM trips WHERE  iDriverId='$iDriverId' AND iActive='Finished' AND eHailTrip='No'";

    $data_count_all = $obj->MySQLSelect($sql_all);

    $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);

    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;

    //$sql  = "SELECT rate.*,DATE_FORMAT(rate.tDate, '%M, %Y') AS tDate FROM ratings_user_driver as rate, trips as tr WHERE  rate.iTripId = tr.iTripId AND tr.iDriverId='$iDriverId' AND tr.iActive='Finished' AND rate.eUserType='Passenger' ORDER BY tr.iTripId DESC". $limit;
    $sql = "SELECT rate.*,CONCAT(ru.vName,' ',ru.vLastName) as vName,ru.iUserId as passengerid,ru.vImgName FROM ratings_user_driver as rate LEFT JOIN trips as tr ON tr.iTripId = rate.iTripId  LEFT JOIN register_user as ru ON ru.iUserId = tr.iUserId WHERE tr.iDriverId='$iDriverId' AND tr.iActive='Finished' AND tr.eHailTrip='No' AND rate.eUserType='Passenger' ORDER BY tr.iTripId DESC" . $limit;

    $Data = $obj->MySQLSelect($sql);
    for ($i = 0; $i < count($Data); $i++) {
        $Data[$i]['vImage'] = $tconfig["tsite_upload_images_passenger"] . '/' . $Data[$i]['passengerid'] . '/3_' . $Data[$i]['vImgName'];
        $Data[$i]['tDateOrig'] = $Data[$i]['tDate'];
        $Data[$i]['tDate'] = $generalobj->DateTime($Data[$i]['tDate'], 14);
    }
    $totalNum = count($Data);

    if (count($Data) > 0) {

        $returnData['message'] = $Data;
        if ($TotalPages > $page) {
            $returnData['NextPage'] = $page + 1;
        } else {
            $returnData['NextPage'] = "0";
        }
        $returnData['vAvgRating'] = strval($vAvgRating);
        $returnData['Action'] = "1";
        echo json_encode($returnData);

    } else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_NO_FEEDBACK";
        echo json_encode($returnData);
    }

?> 