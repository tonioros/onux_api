<?php 

    $usr_email = isset($_REQUEST["usr_email"]) ? $_REQUEST["usr_email"] : '';
    $driver_id = isset($_REQUEST["driver_id"]) ? $_REQUEST["driver_id"] : '';
    $tripID = isset($_REQUEST["tripID"]) ? $_REQUEST["tripID"] : '';
    $rating_1 = isset($_REQUEST["rating_1"]) ? $_REQUEST["rating_1"] : '';

    $message = isset($_REQUEST["message"]) ? $_REQUEST['message'] : '';
    $tripVerificationCode = isset($_REQUEST["verification_code"]) ? $_REQUEST['verification_code'] : '';

    $average_rating = $rating_1;

    $sql = "SELECT iVerificationCode FROM `trips`  WHERE  iTripId='$tripID'";
    $row_code = $obj->MySQLSelect($sql);

    $verificationCode = $row_code[0]['iVerificationCode'];

    // if($tripVerificationCode==$verificationCode){

    $VerificationStatus = "Verified";

    $where = " iTripId = '$tripID'";

    $Data_update_trips['eVerified'] = $VerificationStatus;

    $id = $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where);

    $sql = "SELECT vAvgRating FROM `register_user` WHERE iUserId='$usr_email'";
    $row = $obj->MySQLSelect($sql);

    $average_rating = ($row[0]['vAvgRating'] + $average_rating) / 2;

    $usrType = "Driver";

    $sql = "SELECT * FROM `ratings_user_driver` WHERE iTripId = '$tripID' && eUserType = '$usrType'";
    $row = $obj->MySQLSelect($sql);

    if (count($row) > 0) {
        echo "LBL_RATING_EXIST";

    } else {

        $where = " iUserId = '$usr_email'";

        $Data_update_passenger['vAvgRating'] = round($average_rating, 1);

        $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);

        $Data_update_ratings['iTripId'] = $tripID;
        $Data_update_ratings['vRating1'] = $rating_1;
        $Data_update_ratings['vMessage'] = $message;
        $Data_update_ratings['eUserType'] = $usrType;

        $id = $obj->MySQLQueryPerform("ratings_user_driver", $Data_update_ratings, 'insert');

        if ($id > 0) {
            echo "Ratings Successful.";
        } else {

            echo "Ratings UnSuccessful.";
        }
        sendTripReceiptAdmin($tripID);
    }


?>