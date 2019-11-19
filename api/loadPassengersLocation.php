<?php 

    global $generalobj, $obj;

    /*$iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $radius = isset($_REQUEST["Radius"]) ? $_REQUEST["Radius"] : '';
    $sourceLat = isset($_REQUEST["Latitude"]) ? $_REQUEST["Latitude"] : '';
    $sourceLon = isset($_REQUEST["Longitude"]) ? $_REQUEST["Longitude"] : '';

    $str_date = @date('Y-m-d H:i:s', strtotime('-5 minutes'));

    $sql = "SELECT ROUND(( 3959 * acos( cos( radians(".$sourceLat.") )
     * cos( radians( vLatitude ) )
     * cos( radians( vLongitude ) - radians(".$sourceLon.") )
    + sin( radians(".$sourceLat.") )
     * sin( radians( vLatitude ) ) ) ),2) AS distance, register_driver.*  FROM `register_driver`
    WHERE (vLatitude != '' AND vLongitude != '' AND eStatus='Active' AND tLastOnline > '$str_date')
    HAVING distance < ".$radius." ORDER BY `register_driver`";

    $Data = $obj->MySQLSelect($sql);*/

    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $radius = isset($_REQUEST["Radius"]) ? $_REQUEST["Radius"] : '';
    $sourceLat = isset($_REQUEST["Latitude"]) ? $_REQUEST["Latitude"] : '';
    $sourceLon = isset($_REQUEST["Longitude"]) ? $_REQUEST["Longitude"] : '';

    $str_date = @date('Y-m-d H:i:s', strtotime('-5 minutes'));

    // register_user table
    $sql = "SELECT ROUND(( 3959 * acos( cos( radians(" . $sourceLat . ") )
		* cos( radians( vLatitude ) )
		* cos( radians( vLongitude ) - radians(" . $sourceLon . ") )
		+ sin( radians(" . $sourceLat . ") )
		* sin( radians( vLatitude ) ) ) ),2) AS distance, register_user.*  FROM `register_user`
		WHERE (vLatitude != '' AND vLongitude != '' AND eStatus='Active' AND tLastOnline >  DATE_SUB(NOW(), INTERVAL 1 HOUR)))
		HAVING distance < " . $radius . " ORDER BY `register_user`.iUserId ASC";

    $Data = $obj->MySQLSelect($sql);
    $storeuser = array();
    $storetrip = array();

    foreach ($Data as $value) {

        $dataofuser = array("Type" => 'Online', "Latitude" => $value['vLatitude'], "Longitude" => $value['vLongitude'], "iUserId" => $value['iUserId']);
        array_push($storeuser, $dataofuser);

    }

    // trip table
    if (SITE_TYPE == 'Demo') {
        $sql_trip = "SELECT ROUND(( 3959 * acos( cos( radians(" . $sourceLat . ") )
			* cos( radians( tStartLat ) )
			* cos( radians( tStartLong ) - radians(" . $sourceLon . ") )
			+ sin( radians(" . $sourceLat . ") )
			* sin( radians( tStartLat ) ) ) ),2) AS distance, trips.*  FROM `trips`
			WHERE (tStartLat != '' AND tStartLong != '' AND tTripRequestDate >= DATE_SUB(CURDATE(), INTERVAL 1 HOUR))
			HAVING distance < " . $radius . " ORDER BY `trips`.iTripId DESC";
    } else {
        $sql_trip = "SELECT ROUND(( 3959 * acos( cos( radians(" . $sourceLat . ") )
			* cos( radians( tStartLat ) )
			* cos( radians( tStartLong ) - radians(" . $sourceLon . ") )
			+ sin( radians(" . $sourceLat . ") )
			* sin( radians( tStartLat ) ) ) ),2) AS distance, trips.*  FROM `trips`
			WHERE (tStartLat != '' AND tStartLong != '' AND tTripRequestDate >= DATE_SUB(NOW(), INTERVAL 1 HOUR))
			HAVING distance < " . $radius . " ORDER BY `trips`.iTripId DESC";
    }

    $Dataoftrips = $obj->MySQLSelect($sql_trip);

    foreach ($Dataoftrips as $value1) {

        $valuetrip = array("Type" => 'History', "Latitude" => $value1['tStartLat'], "Longitude" => $value1['tStartLong'], "iTripId" => $value1['iTripId']);
        array_push($storetrip, $valuetrip);

    }

    $finaldata = array_merge($storeuser, $storetrip);
    //echo "<pre>"; print_r($finaldata); exit;

    if (count($finaldata) > 0) {
        $returnData['Action'] = "1";
        $returnData['message'] = $finaldata;
    } else {
        $returnData['Action'] = "0";
    }
    echo json_encode($returnData);


?>