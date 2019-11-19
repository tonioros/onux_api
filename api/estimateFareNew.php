<?php 

    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $distance = isset($_REQUEST["distance"]) ? $_REQUEST["distance"] : '';
    $time = isset($_REQUEST["time"]) ? $_REQUEST["time"] : '';
    $SelectedCar = isset($_REQUEST["SelectedCar"]) ? $_REQUEST["SelectedCar"] : '';
    $StartLatitude = isset($_REQUEST["StartLatitude"]) ? $_REQUEST["StartLatitude"] : '0.0';
    $EndLongitude = isset($_REQUEST["EndLongitude"]) ? $_REQUEST["EndLongitude"] : '0.0';
    $DestLatitude = isset($_REQUEST["DestLatitude"]) ? $_REQUEST["DestLatitude"] : '';
    $DestLongitude = isset($_REQUEST["DestLongitude"]) ? $_REQUEST["DestLongitude"] : '';
    $iQty = isset($_REQUEST["iQty"]) ? $_REQUEST["iQty"] : '1';
    $PromoCode = isset($_REQUEST["PromoCode"]) ? $_REQUEST["PromoCode"] : '';
    $SelectedCarTypeID = isset($_REQUEST["SelectedCarTypeID"]) ? $_REQUEST["SelectedCarTypeID"] : '';

    $time = round(($time / 60), 2);
    $distance = round(($distance / 1000), 2);

    $isDestinationAdded = "No";
    if ($DestLatitude != "" && $DestLongitude != "") {
        $isDestinationAdded = "Yes";
    }

    $sourceLocationArr = array($StartLatitude, $EndLongitude);
    $destinationLocationArr = array($DestLatitude, $DestLongitude);

    ######### Checking For Flattrip #########
    /*if($isDestinationAdded == "Yes"){
    $sourceLocationArr = array($StartLatitude,$EndLongitude);
    $destinationLocationArr = array($DestLatitude,$DestLongitude);
    $data_flattrip = checkFlatTripnew($sourceLocationArr,$destinationLocationArr);
    $eFlatTrip = $data_flattrip['eFlatTrip'];
    $fFlatTripPrice = $data_flattrip['Flatfare'];
    }else{
    $eFlatTrip = "No";
    $fFlatTripPrice = 0;
    }     */
    ######### Checking For Flattrip #########
    //$Fare_data=calculateFareEstimateAll($time,$distance,$SelectedCar,$iUserId,1);
    $Fare_data = calculateFareEstimateAll($time, $distance, $SelectedCar, $iUserId, 1, "", "", $PromoCode, 1, 0, 0, 0, "", "Passenger", $iQty, $SelectedCarTypeID, $isDestinationAdded, $eFlatTrip, $fFlatTripPrice, $sourceLocationArr, $destinationLocationArr);
    //    $Fare_data[0]['FinalFare']=936;
    //    $Fare_data[0]['iBaseFare']=936;
    //    if($iUserId==1349 ||$iUserId==32016){
    //$myfile = fopen("example.txt", "w") or die("Unable to open file!");



    $varfor = $Fare_data[0]['total_fare'];
    $varfor = explode(" ", $varfor);
    $varfor[1] = str_replace(',', '', $varfor[1]);
    //$sessionid=get_value('register_user', 'tSessionId', 'iUserId',$iUserId,'','true');
    $tripestimatefare['User_Id'] = $iUserId;
    $tripestimatefare['Estimate_Fare'] = $varfor[1];

    $getuserid = get_value('estimatefare', 'User_Id', 'User_Id', $iUserId, '', 'true');
  //    fwrite($myfile, $getuserid."\n");
    if ($getuserid == $iUserId) {
        $tripestimatefare1['Estimate_Fare'] = $varfor[1];
        $where = " User_Id = '" . $iUserId . "'";
        $id = $obj->MySQLQueryPerform("estimatefare", $tripestimatefare1, 'update', $where);
        //fwrite($myfile, "in update \n".$varfor[1]."\n");
    } else {

        $obj->MySQLQueryPerform("estimatefare", $tripestimatefare, 'insert');
        // fwrite($myfile, "in insert \n");
    }
   //    fclose($myfile);
    //    }
    $returnArr["Action"] = "1";
    $returnArr["message"] = $Fare_data;
    //$returnArr['eFlatTrip'] = $eFlatTrip;
    echo json_encode($returnArr);

?>