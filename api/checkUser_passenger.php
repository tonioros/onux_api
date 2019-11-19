<?php 

    $Emid = isset($_REQUEST["Email"]) ? $_REQUEST["Email"] : '';
    $Phone = isset($_REQUEST["Phone"]) ? $_REQUEST["Phone"] : '';

    $sql = "SELECT vEmail,vPhone FROM `register_user` WHERE vEmail = '$Emid' OR vPhone = '$Phone'";
    $Data = $obj->MySQLSelect($sql);

    if (count($Data) > 0) {

        if ($Emid == $Data[0]['vEmail']) {
            echo "EMAIL_EXIST";
        } else {
            echo "MOBILE_EXIST";
        }
    } else {
        echo "NO_REG_FOUND";
    }


?>