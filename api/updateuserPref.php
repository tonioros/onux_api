<?php 
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Driver';
    $eFemaleOnly = isset($_REQUEST['eFemaleOnly']) ? clean($_REQUEST['eFemaleOnly']) : 'No';

    $where = " iDriverId = '$iMemberId'";
    $Data_update_User['eFemaleOnlyReqAccept'] = $eFemaleOnly;

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_User, 'update', $where);

    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = getDriverDetailInfo($iMemberId);
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    echo json_encode($returnArr);

?>
