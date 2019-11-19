<?php 
    global $generalobj, $obj;

    $iDriverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';
    $year = isset($_REQUEST["year"]) ? $_REQUEST["year"] : @date('Y');
    if ($year == "") {
        $year = @date('Y');
    }

    $vCurrencyDriver = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $iDriverId, '', 'true');
    $vCurrencySymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyDriver, '', 'true');

    $start = @date('Y');
    $end = '1970';
    $year_arr = array();
    for ($j = $start; $j >= $end; $j--) {
        $year_arr[] = strval($j);
    }

    $Month_Array = array('01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec');

    $sql = "SELECT * FROM trips WHERE iDriverId='" . $iDriverId . "' AND tTripRequestDate LIKE '" . $year . "%'";
    $tripData = $obj->MySQLSelect($sql);
    $totalEarnings = 0;

    //if(count($tripData) > 0){
    for ($i = 0; $i < count($tripData); $i++) {
        $iFare = $tripData[$i]['fTripGenerateFare'];
        $fCommision = $tripData[$i]['fCommision'];
        $priceRatio = $tripData[$i]['fRatio_' . $vCurrencyDriver];
        $totalEarnings += ($iFare - $fCommision) * $priceRatio;
    }

    $yearmontharr = array();
    $yearmontearningharr_Max = array();
    foreach ($Month_Array as $key => $value) {
        $tripyearmonthdate = $year . "-" . $key;
        $sql_Month = "SELECT * FROM trips WHERE iDriverId='" . $iDriverId . "' AND tTripRequestDate LIKE '" . $tripyearmonthdate . "%'";
        $tripyearmonthData = $obj->MySQLSelect($sql_Month);
        $tripData_M = strval(count($tripyearmonthData));
        $yearmontearningharr = array();
        $totalEarnings_M = 0;
        for ($j = 0; $j < count($tripyearmonthData); $j++) {
            $iFare_M = $tripyearmonthData[$j]['fTripGenerateFare'];
            $fCommision_M = $tripyearmonthData[$j]['fCommision'];
            $priceRatio_M = $tripyearmonthData[$j]['fRatio_' . $vCurrencyDriver];
            $totalEarnings_M += ($iFare_M - $fCommision_M) * $priceRatio_M;
        }
        $yearmontearningharr_Max[] = $totalEarnings_M;
        $yearmontearningharr["CurrentMonth"] = $value;
        $yearmontearningharr["TotalEarnings"] = strval(round($totalEarnings_M < 0 ? 0 : $totalEarnings_M, 1));
        $yearmontearningharr["TripCount"] = strval(round($tripData_M, 1));
        array_push($yearmontharr, $yearmontearningharr);
    }
    foreach ($yearmontearningharr_Max as $key => $value) {
        if ($value >= $max) {
            $max = $value;
        }

    }
    $returnArr['Action'] = "1";
    $returnArr['TotalEarning'] = $vCurrencySymbol . " " . strval(round($totalEarnings, 1));
    $returnArr['TripCount'] = strval(count($tripData));
    $returnArr["CurrentYear"] = $year;
    $returnArr['MaxEarning'] = strval($max);
    $returnArr['YearMonthArr'] = $yearmontharr;
    $returnArr['YearArr'] = $year_arr;
    /*}else{
    $returnArr['Action'] = "0";
    } */

    echo json_encode($returnArr);

?>