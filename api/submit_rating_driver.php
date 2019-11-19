<?php 


    $usr_email = isset($_REQUEST["usr_email"]) ? $_REQUEST["usr_email"] : '';
    $driver_id = isset($_REQUEST["driver_id"]) ? $_REQUEST["driver_id"] : '';
    $tripID = isset($_REQUEST["tripID"]) ? $_REQUEST["tripID"] : '';
    $rating_1 = isset($_REQUEST["rating_1"]) ? $_REQUEST["rating_1"] : '';
    $message = isset($_REQUEST["message"]) ? $_REQUEST['message'] : '';
    $tripVerificationCode = isset($_REQUEST["verification_code"]) ? $_REQUEST['verification_code'] : '';
    //$average_rating=($rating_1+$rating_2+$rating_3+$rating_4)/4 ;

    $average_rating = $rating_1;

    $usrType = "Passenger";

    $sql = "SELECT * FROM `ratings_user_driver` WHERE iTripId = '$tripID' and eUserType = '$usrType'";
    $row_check = $obj->MySQLSelect($sql);

    $sql = "SELECT vAvgRating FROM `register_driver` WHERE iDriverId = '$driver_id'";
    $row = $obj->MySQLSelect($sql);

    $average_rating = ($row[0]['vAvgRating'] + $average_rating) / 2;

    if (count($row_check) > 0) {

        echo "LBL_RATING_EXIST";

    } else {

        $where = " iDriverId = '$driver_id'";

        $Data_update_driver['vAvgRating'] = round($average_rating, 1);

        $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

        $Data_update_ratings['iTripId'] = $tripID;
        $Data_update_ratings['vRating1'] = $rating_1;
        $Data_update_ratings['vMessage'] = $message;
        $Data_update_ratings['eUserType'] = $usrType;

        $id = $obj->MySQLQueryPerform("ratings_user_driver", $Data_update_ratings, 'insert');

        if ($id) {
            echo "Ratings Successful.";
        } else {

            echo "Ratings UnSuccessful.";
        }

        sendTripReceipt($tripID);

    }


?>