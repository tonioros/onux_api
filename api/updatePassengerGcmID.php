<?php 
    $user_id_auto = isset($_REQUEST["UidAuto"]) ? $_REQUEST['UidAuto'] : '';
    $GcmID = isset($_REQUEST["GcmId"]) ? $_REQUEST['GcmId'] : '';

    $where = " iUserId = '" . $user_id_auto . "'";
    $Data['iGcmRegId'] = $GcmID;
    $id = $obj->MySQLQueryPerform("register_user", $Data, 'update', $where);

    if ($id) {
        echo "Update Successful..";
    } else {
        echo "No Update.";
    }


?>