<?php 
    $Source_point_Address = isset($_REQUEST["sAddress"]) ? $_REQUEST["sAddress"] : '';
    $Dest_point_Address = isset($_REQUEST["dAddress"]) ? $_REQUEST["dAddress"] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iVehicleTypeId = isset($_REQUEST["iVehicleTypeId"]) ? $_REQUEST["iVehicleTypeId"] : '';

    $vCurrencyPassenger = get_value('register_user', 'vCurrencyPassenger', 'iUserId', $iUserId, '', 'true');
    $priceRatio = get_value('currency', 'Ratio', 'vName', $vCurrencyPassenger, '', 'true');
    $currencySymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyPassenger, '', 'true');
    $sourceLocation = isset($_REQUEST["sourceLocation"]) ? $_REQUEST["sourceLocation"] : '';
    $destinationLocation = isset($_REQUEST["destinationLocation"]) ? $_REQUEST["destinationLocation"] : '';
    $Source_point_AddressArr = explode(",", $sourceLocation);
    $Dest_point_AddressArr = explode(",", $destinationLocation);
    $data = checkFlatTripnew($Source_point_AddressArr, $Dest_point_AddressArr, $iVehicleTypeId);
    $fFlatTripPrice = $data['Flatfare'];
    $data['passenger_price'] = $currencySymbol . " " . number_format(($fFlatTripPrice * $priceRatio), 2);
    echo json_encode($data);exit;

?>