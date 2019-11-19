<?php 

global $generalobj;

    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : 'Ride';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';

    $vLanguage = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    if ($vLanguage == "" || $vLanguage == null) {
        $vLanguage = "EN";
    }

    $per_page = 10;
    $sql_all = "SELECT COUNT(iTripId) As TotalIds FROM trips WHERE  iUserId='$iUserId' AND (iActive='Canceled' || iActive='Finished')";
    $data_count_all = $obj->MySQLSelect($sql_all);
    $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);

    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;

    //$sql = "SELECT tripRate.vRating1 as TripRating,tr.* FROM `trips` as tr,`ratings_user_driver` as tripRate  WHERE  tr.iUserId='$iUserId' AND tr.eType='$eType' AND tripRate.iTripId=tr.iTripId AND tripRate.eUserType='$UserType' AND (tr.iActive='Canceled' || tr.iActive='Finished') ORDER BY tr.iTripId DESC" . $limit;
    $sql = "SELECT tr.* FROM `trips` as tr WHERE tr.iUserId='$iUserId' AND (tr.iActive='Canceled' || tr.iActive='Finished') ORDER BY tr.iTripId DESC" . $limit;
    $Data = $obj->MySQLSelect($sql);
    $totalNum = count($Data);

    $i = 0;
    if (count($Data) > 0) {

        while (count($Data) > $i) {

            $returnArr = getTripPriceDetails($Data[$i]['iTripId'], $iUserId, "Passenger");

            $sql = "SELECT count(iRatingId) AS Total FROM `ratings_user_driver` WHERE iTripId = '" . $Data[$i]['iTripId'] . "' and eUserType = '$UserType'";
            $rating_check = $obj->MySQLSelect($sql);
            $returnArr['is_rating'] = 'No';
            if ($rating_check[0]['Total'] > 0) {
                $returnArr['is_rating'] = 'Yes';
            }

            $Data[$i] = array_merge($Data[$i], $returnArr);
            if ($Data[$i]["eType"] == 'UberX' && $Data[$i]["eFareType"] != "Regular") {
                $Data[$i]['tDaddress'] = "";
            }
            /*Added For Rental*/
            if ($Data[$i]['iRentalPackageId'] > 0) {
                $rentalData = getRentalData($Data[$i]['iRentalPackageId']);
                $Data[$i]['vPackageName'] = $rentalData[0]['vPackageName_' . $vLanguage];
            } else {
                $Data[$i]['vPackageName'] = "";
            }
            /*End Added For Rental*/
            $i++;
        }
        $returnData['message'] = $Data;
        if ($TotalPages > $page) {
            $returnData['NextPage'] = "" . ($page + 1);
        } else {
            $returnData['NextPage'] = "0";
        }
        $returnData['Action'] = "1";
        echo json_encode($returnData);

    } else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_NO_DATA_AVAIL";
        echo json_encode($returnData);
    }

