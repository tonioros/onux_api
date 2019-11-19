<?php  
    // $startDate="2016-04-04 14:33:58";

    // echo date('dS M \a\t h:i a',strtotime($startDate));
    // $value= get_value('user_emergency_contact', 'COUNT(iEmergencyId) as Count', 'iUserId', "34");
    // echo $value[0]['Count'];
    // echo $res = preg_replace("/[^0-9]/", "", "Every 6.1,0--//+2 Months" );

    /* $tripID    = isset($_REQUEST["tripID"]) ? $_REQUEST["tripID"] : '';
    $rating  = isset($_REQUEST["rating"]) ? $_REQUEST["rating"] : '';

    $iUserId =get_value('trips', 'iUserId', 'iTripId',$tripID,'','true');
    $tableName = "register_user";
    $where = " WHERE iUserId='".$iUserId."'";

    $sql = "SELECT vAvgRating FROM ".$tableName.' '.$where;
    $fetchAvgRating= $obj->MySQLSelect($sql);

    $fetchAvgRating[0]['vAvgRating'] = floatval($fetchAvgRating[0]['vAvgRating']);
    // echo  "Fetch:".$fetchAvgRating[0]['vAvgRating'];exit;

    if($fetchAvgRating[0]['vAvgRating'] > 0){
    $average_rating = round(($fetchAvgRating[0]['vAvgRating'] + $rating) / 2,1);
    }else{
    $average_rating = round($fetchAvgRating[0]['vAvgRating'] + $rating,1);
    }

    $Data_update['vAvgRating']=$average_rating;

    echo "AvgRate:".$Data_update['vAvgRating']; */

    $langCodesArr = get_value('language_master', 'vCode', '', '');

    //print_r($langCodesArr);

    //echo "<BR/>";

    for ($i = 0; $i < count($langCodesArr); $i++) {
        $currLngCode = $langCodesArr[$i]['vCode'];
        $vVehicleType = $langCodesArr[$i]['vVehicleType'];
        $fieldName = "vVehicleType_" . $currLngCode;
        $suffixName = $i == 0 ? "vVehicleType" : "vVehicleType_" . $langCodesArr[$i - 1]['vCode'];

        $sql = "ALTER TABLE vehicle_type ADD " . $fieldName . " VARCHAR(50) AFTER" . " " . $suffixName;
        $id = $obj->sql_query($sql);
    }

    $vehicleTypesArr = get_value('vehicle_type', 'vVehicleType,iVehicleTypeId', '', '');

    for ($j = 0; $j < count($vehicleTypesArr); $j++) {
        $vVehicleType = $vehicleTypesArr[$j]['vVehicleType'];
        $iVehicleTypeId = $vehicleTypesArr[$j]['iVehicleTypeId'];

        //echo "vVehicleType:".$vVehicleType."<BR/>";
        for ($k = 0; $k < count($langCodesArr); $k++) {
            $currLngCode = $langCodesArr[$k]['vCode'];
            $fieldName = "vVehicleType_" . $currLngCode;
            $suffixName = $k == 0 ? "vVehicleType" : "vVehicleType_" . $langCodesArr[$k - 1]['vCode'];

            // $sql = "ALTER TABLE vehicle_type ADD ".$fieldName." VARCHAR(50) AFTER"." ".$suffixName;
            // $id= $obj->sql_query($sql);
            echo $sql = "UPDATE `vehicle_type` SET " . $fieldName . " = '" . $vVehicleType . "' WHERE iVehicleTypeId = '$iVehicleTypeId'";
            echo "<br/>";
            $id1 = $obj->sql_query($sql);

            echo "<br/>" . $id1;
        }

    }

    // echo $sql = "UPDATE `vehicle_type` SET ".$fieldName." = ".$vVehicleType;
    // $id1= $obj->sql_query($sql);
    // echo "<br/>".$id;

