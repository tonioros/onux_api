<?php
include 'conn.php';
$body = file_get_contents("php://input");
$webhook = json_decode($body, true);
$conn = OpenCon();
//$userid=$obj->_type;
if($webhook["_type"]="error"){
$sql = "UPDATE `order_outsource` SET `response`='hh2".$webhook["code"]."' WHERE srno=1";
}else{
    $sql = "UPDATE `order_outsource` SET `tracking_url`='".$webhook["tracking_url"].",`response`='".$webhook["_type"]."' WHERE srno=1";
}
if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
$conn->close();

?>