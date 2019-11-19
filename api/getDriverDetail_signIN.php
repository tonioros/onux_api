<?php 
    $Driver_email = $_REQUEST["DriverId"];
    $Password_driver = $generalobj->encrypt($_REQUEST["Pass"]);
    $GCMID = isset($_REQUEST["GCMID"]) ? $_REQUEST["GCMID"] : '';
    $vFirebaseDeviceToken = isset($_REQUEST["vFirebaseDeviceToken"]) ? $_REQUEST["vFirebaseDeviceToken"] : '';

    $DeviceType = "Android";
    $sql = "SELECT rd.iDriverId,rd.eStatus,cmp.eStatus as cmpEStatus FROM `register_driver` as rd,`company` as cmp WHERE rd.vEmail='$Driver_email'  AND rd.vPassword='$Password_driver' AND cmp.iCompanyId=rd.iCompanyId";
    $Data = $obj->MySQLSelect($sql);

    if (count($Data) > 0) {

        if ($Data[0]['eStatus'] != "Deleted") {
            if ($GCMID != '') {

                $iDriverId_driver = $Data[0]['iDriverId'];
                $where = " iDriverId = '$iDriverId_driver' ";

                $Data_update_driver['iGcmRegId'] = $GCMID;
                $Data_update_driver['eDeviceType'] = $DeviceType;
                $Data_update_driver['vFirebaseDeviceToken'] = $vFirebaseDeviceToken;
                $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

            }
            echo json_encode(getDriverDetailInfo($Data[0]['iDriverId'], 1));

        } else {
            echo "ACC_DELETED";
        }
    } else {
        $sql = "SELECT * FROM `register_driver` WHERE vEmail='$Driver_email'";
        $num_rows_Email = $obj->MySQLSelect($sql);
        if (count($num_rows_Email) == 1) {
            echo "LBL_PASSWORD_ERROR_TXT";
        } else {
            echo "LBL_NO_REG_FOUND";
        }
    }


?>