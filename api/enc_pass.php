<?php 

    $pass = isset($_REQUEST['pass']) ? clean($_REQUEST['pass']) : '';

    echo $generalobj->encrypt($pass);

?>