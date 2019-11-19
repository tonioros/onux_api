<?php 

    $tripId = isset($_REQUEST["TripId"]) ? $_REQUEST["TripId"] : '';
    $latitudes = isset($_REQUEST['latList']) ? $_REQUEST['latList'] : '';
    $longitudes = isset($_REQUEST['lonList']) ? $_REQUEST['lonList'] : '';
    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';

    if ($iDriverId != "" && $tripId == "") {

        $iTripId = get_value('register_driver', 'iTripId', 'iDriverId', $iDriverId, '', 'true');
        if ($iTripId != "") {
            $tripId = $iTripId;
        }
    }

    if ($tripId != '' && $latitudes != '' && $longitudes != '') {
        $latitudes = preg_replace("/[^0-9,.-]/", "", $latitudes);
        $longitudes = preg_replace("/[^0-9,.-]/", "", $longitudes);
        $id = processTripsLocations($tripId, $latitudes, $longitudes);
    }

    if ($id > 0) {
        $returnArr['Action'] = "1";
    } else {
        $returnArr['Action'] = "0";
    }

    echo json_encode($returnArr);

?>