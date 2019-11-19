<?php 

    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';

    $sql = "SELECT vWorkLocation,vWorkLocationLatitude,vWorkLocationLongitude,vWorkLocationRadius,eSelectWorkLocation FROM `register_driver` WHERE iDriverId = '" . $iDriverId . "'";
    $Data = $obj->MySQLSelect($sql);

    if (count($Data) > 0) {
        $vWorkLocationRadius = $Data[0]['vWorkLocationRadius'];
        $vCountryUnitDriver = getMemberCountryUnit($iDriverId, "Driver");
        $Data[0]['vCountryUnitDriver'] = $vCountryUnitDriver;
        if ($vCountryUnitDriver == "Miles") {
            $Data[0]['vWorkLocationRadius'] = round($vWorkLocationRadius * 0.6213711, 2); // convert miles to km
            $vWorkLocationRadius = $Data[0]['vWorkLocationRadius'];
        }

        $radiusArr = array(1, 2, 3, 5);
        if (!in_array($vWorkLocationRadius, $radiusArr)) {
            array_push($radiusArr, $vWorkLocationRadius);
        }

        $radusArr = array();
        for ($i = 0; $i < count($radiusArr); $i++) {
            $radusArr[$i]['value'] = $radiusArr[$i];
            $radusArr[$i]['eUnit'] = $vCountryUnitDriver;
            $radusArr[$i]['eSelected'] = ($vWorkLocationRadius == $radiusArr[$i]) ? "Yes" : "No";
        }
        $Data[0]['RadiusList'] = $radusArr;
        $returnArr['Action'] = "1";
        $returnArr['message'] = $Data[0];
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    $obj->MySQLClose();
    echo json_encode($returnArr);exit;

?>