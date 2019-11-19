<?php 

    //$iGeneralUserId = isset($_REQUEST["iGeneralUserId"]) ? $_REQUEST["iGeneralUserId"] : ''; // for both driver or passenger
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : ''; // for both driver or passenger
    $tripID = isset($_REQUEST["tripID"]) ? $_REQUEST["tripID"] : '';
    $rating = isset($_REQUEST["rating"]) ? $_REQUEST["rating"] : '';
    $message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger'; // Passenger or Driver
    $fAmount = isset($_REQUEST["fAmount"]) ? $_REQUEST["fAmount"] : '';
    $isCollectTip = isset($_REQUEST["isCollectTip"]) ? $_REQUEST["isCollectTip"] : '';
    if ($isCollectTip == "" || $isCollectTip == null) {
        $isCollectTip = "No";
    }
    $eType = get_value('trips', 'eType', 'iTripId', $tripID, '', 'true');

    $message = stripslashes($message);

    $sql = "SELECT * FROM `ratings_user_driver` WHERE iTripId = '$tripID' and eUserType = '$userType'";
    $row_check = $obj->MySQLSelect($sql);

    //$ENABLE_TIP_MODULE=$generalobj->getConfigurations("configurations","ENABLE_TIP_MODULE");

    if (count($row_check) > 0) {
        // $returnArr['Action'] = "0"; //LBL_RATING_EXIST
        // $returnArr['message'] = "LBL_ERROR_RATING_SUBMIT_AGAIN_TXT"; //LBL_RATING_EXIST
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_TRIP_FINISHED_TXT";
        echo json_encode($returnArr);exit;
    } else {

        # Code For Tip Charge #
        if ($isCollectTip == "Yes" && $userType == "Passenger") {
            if ($fAmount > 0) {
                TripCollectTip($iMemberId, $tripID, $fAmount);
            }
        }
        # Code For Tip Charge #

        if ($userType == "Passenger") {
            $iDriverId = get_value('trips', 'iDriverId', 'iTripId', $tripID, '', 'true');
            $tableName = "register_driver";
            $where = "iDriverId='" . $iDriverId . "'";
            $iMemberId = $iDriverId;
        } else {

            $where_trip = " iTripId = '$tripID'";

            $Data_update_trips['eVerified'] = "Verified";

            $id = $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where_trip);

            $iUserId = get_value('trips', 'iUserId', 'iTripId', $tripID, '', 'true');
            $tableName = "register_user";
            $where = "iUserId='" . $iUserId . "'";
            $iMemberId = $iUserId;
        }
        /* Insert records into ratings table*/
        $Data_update_ratings['iTripId'] = $tripID;
        $Data_update_ratings['vRating1'] = $rating;
        $Data_update_ratings['vMessage'] = $message;
        $Data_update_ratings['eUserType'] = $userType;

        $id = $obj->MySQLQueryPerform("ratings_user_driver", $Data_update_ratings, 'insert');

        /* Set average rating for passenger OR Driver */
        // Driver gives rating to passenger and passenger gives rating to driver
        /*$average_rating = getUserRatingAverage($iMemberId,$userType);

        $sql = "SELECT vAvgRating FROM ".$tableName.' WHERE '.$where;
        $fetchAvgRating= $obj->MySQLSelect($sql);

        if($fetchAvgRating[0]['vAvgRating'] > 0){
        $average_rating = round(($fetchAvgRating[0]['vAvgRating'] + $rating) / 2,1);
        }else{
        $average_rating = round($fetchAvgRating[0]['vAvgRating'] + $rating,1);
        } */

        $Data_update['vAvgRating'] = getUserRatingAverage($iMemberId, $userType);

        $id = $obj->MySQLQueryPerform($tableName, $Data_update, 'update', $where);

        if ($id) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_TRIP_FINISHED_TXT";
            $returnArr['eType'] = $eType;
            $vTripPaymentMode = get_value('trips', 'vTripPaymentMode', 'iTripId', $tripID, '', 'true');
            if ($vTripPaymentMode == "Card") {
                $returnArr['ENABLE_TIP_MODULE'] = $ENABLE_TIP_MODULE;
            } else {
                $returnArr['ENABLE_TIP_MODULE'] = "No";
            }
            echo json_encode($returnArr);
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            echo json_encode($returnArr);
        }

        if ($userType == "Passenger") {
            sendTripReceipt($tripID);
        } else {
            sendTripReceiptAdmin($tripID);
        }
        // echo "come";
    }


?>