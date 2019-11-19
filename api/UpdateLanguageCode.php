<?php 

    $lCode = isset($_REQUEST['vCode']) ? clean(strtoupper($_REQUEST['vCode'])) : ''; // User's prefered language
    $UserID = isset($_REQUEST['UserID']) ? clean($_REQUEST['UserID']) : '';
    $UserType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : '';

    if ($UserType == "Passenger") {

        $where = " iUserId = '$UserID'";
        $Data_update_passenger['vLang'] = $lCode;

        $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);
        // echo $id; exit;
        if ($id < 0) {
            echo "UpdateFailed";
            exit;
        }
    } else if ($UserType == "Driver") {
        $where = " iDriverId = '$UserID'";
        $Data_update_driver['vLang'] = $lCode;

        $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
        // echo $id; exit;
        if ($id < 0) {
            echo "UpdateFailed";
            exit;
        }
    }

    /* find default language of website set by admin */
    if ($lCode == '') {
        $sql = "SELECT  `vCode` FROM  `language_master` WHERE eStatus = 'Active' AND `eDefault` = 'Yes' ";
        $default_label = $obj->MySQLSelect($sql);

        $lCode = (isset($default_label[0]['vCode']) && $default_label[0]['vCode']) ? $default_label[0]['vCode'] : 'EN';
    }

    $sql = "SELECT  `vLabel` , `vValue`  FROM  `language_label`  WHERE  `vCode` = '" . $lCode . "' ";
    $all_label = $obj->MySQLSelect($sql);

    $x = array();
    for ($i = 0; $i < count($all_label); $i++) {
        $vLabel = $all_label[$i]['vLabel'];
        $vValue = $all_label[$i]['vValue'];
        $x[$vLabel] = $vValue;
    }
    $x['vCode'] = $lCode; // to check in which languge code it is loading

    echo json_encode($x);


?>