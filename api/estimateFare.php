<?php 

    $sourceLocation = isset($_REQUEST["sourceLocation"]) ? $_REQUEST["sourceLocation"] : '';
    $destinationLocation = isset($_REQUEST["destinationLocation"]) ? $_REQUEST["destinationLocation"] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $distance = isset($_REQUEST["distance"]) ? $_REQUEST["distance"] : '';
    $time = isset($_REQUEST["time"]) ? $_REQUEST["time"] : '';
    $SelectedCar = isset($_REQUEST["SelectedCar"]) ? $_REQUEST["SelectedCar"] : '';

    $sourceLocationArr = explode(",", $sourceLocation);
    $destinationLocationArr = explode(",", $destinationLocation);
    /*$vCurrencyPassenger=get_value('register_user', 'vCurrencyPassenger', 'iUserId', $iUserId,'','true');
    $priceRatio=get_value('currency', 'Ratio', 'vName', $vCurrencyPassenger,'','true');*/
    $sqlp = "SELECT ru.vCurrencyPassenger,cu.Ratio FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iUserId . "'";
    $passengerData = $obj->MySQLSelect($sqlp);
    $vCurrencyPassenger = $passengerData[0]['vCurrencyPassenger'];
    $priceRatio = $passengerData[0]['Ratio'];
    $data_flattrip = checkFlatTripnew($sourceLocationArr, $destinationLocationArr, $SelectedCar);
    $eFlatTrip = $data_flattrip['eFlatTrip'];
    $fFlatTripPrice = $data_flattrip['Flatfare'];

    if ($eFlatTrip == "No") {
        $Fare_data = calculateFareEstimate($time, $distance, $SelectedCar, $iUserId, 1);

        $Fare_data[0]['Distance'] = $distance == null ? "0" : strval(round($distance, 2));
        $Fare_data[0]['Time'] = $time == null ? "0" : strval(round($time, 2));
        $Fare_data[0]['total_fare'] = number_format(round($Fare_data[0]['total_fare'] * $priceRatio, 1), 2);
        $Fare_data[0]['iBaseFare'] = number_format(round($Fare_data[0]['iBaseFare'] * $priceRatio, 1), 2);
        $Fare_data[0]['fPricePerMin'] = number_format(round($Fare_data[0]['fPricePerMin'] * $priceRatio, 1), 2);
        $Fare_data[0]['fPricePerKM'] = number_format(round($Fare_data[0]['fPricePerKM'] * $priceRatio, 1), 2);
        $Fare_data[0]['fCommision'] = number_format(round($Fare_data[0]['fCommision'] * $priceRatio, 1), 2);
        $Fare_data[0]['eFlatTrip'] = "No";
        if ($Fare_data[0]['MinFareDiff'] > 0) {
            $Fare_data[0]['MinFareDiff'] = number_format(round($Fare_data[0]['MinFareDiff'] * $priceRatio, 1), 2);
        } else {
            $Fare_data[0]['MinFareDiff'] = "0";
        }
        $Fare_data[0]['MinFareDiff'] = "0";
    } else {
        $Fare_data[0]['Distance'] = "0.00";
        $Fare_data[0]['Time'] = "0.00";
        $Fare_data[0]['total_fare'] = $data_flattrip['Flatfare']; //number_format(round($fFlatTripPrice * $priceRatio,1),2);
        $Fare_data[0]['iBaseFare'] = number_format(round($fFlatTripPrice * $priceRatio, 1), 2);
        $Fare_data[0]['fPricePerMin'] = "0.00";
        $Fare_data[0]['fPricePerKM'] = "0.00";
        $Fare_data[0]['fCommision'] = number_format(round($fFlatTripPrice * $priceRatio, 1), 2);
        $Fare_data[0]['eFlatTrip'] = "Yes";
        $Fare_data[0]['MinFareDiff'] = "0.00";
        $Fare_data[0]['Flatfare'] = $data_flattrip['Flatfare'];
    }
    $Fare_data[0]['Action'] = "1";

    echo json_encode($Fare_data[0]);

?>