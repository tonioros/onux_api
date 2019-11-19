<?php 

    $tripId = isset($_REQUEST["TripId"]) ? $_REQUEST["TripId"] : '';

    $sql = "SELECT eVerified FROM trips WHERE iTripId=$tripId";

    $result_eVerified = $obj->MySQLSelect($sql);

    if ($result_eVerified[0]['eVerified'] == "Verified") {
        echo "Verified";
    } else {
        echo "Not Verified";
    }


?>