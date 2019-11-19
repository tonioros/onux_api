<?php 
    include_once 'main.inc.php';

        $Emid = isset($_REQUEST["Email"]) ? $_REQUEST["Email"] : '';
        $Phone = isset($_REQUEST["Phone"]) ? $_REQUEST["Phone"] : '';
        $fbid = isset($_REQUEST["fbid"]) ? $_REQUEST["fbid"] : '';
   
        $sql = "SELECT vEmail,vPhone,vFbId FROM register_user WHERE 1=1 AND IF('$Emid'!='',vEmail = '$Emid',0) OR IF('$Phone'!='',vPhone = '$Phone',0) OR IF('$fbid'!='',vFbId = '$fbid',0)";
        $Data = $obj->MySQLSelect($sql);
    
        if (count($Data) > 0) {
    
            $returnArr['Action'] = "0";
    
            if ($Emid == $Data[0]['vEmail']) {
                $returnArr['message'] = "LBL_ALREADY_REGISTERED_TXT";
            } else if ($Phone == $Data[0]['vPhone']) {
                $returnArr['message'] = "LBL_MOBILE_EXIST";
            } else {
                $returnArr['message'] = "LBL_FACEBOOK_ACC_EXIST";
            }
        } else {
            $returnArr['Action'] = "1";
        }
    
        echo json_encode($returnArr);
        exit;
?>