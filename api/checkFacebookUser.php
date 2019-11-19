<?php 

    $FbID = isset($_REQUEST["FbID"]) ? $_REQUEST["FbID"] : '';
    $EmailID = isset($_REQUEST["EmailID"]) ? $_REQUEST["EmailID"] : '';

    $sql = "SELECT iUserId FROM `register_user` WHERE vFbId=" . $FbID . " OR vEmail='$EmailID' ";
    $row = $obj->MySQLSelect($sql);

    if (count($row) > 0) {
        echo "Failed";
    } else {
        echo "success";
    }
    exit;

?>