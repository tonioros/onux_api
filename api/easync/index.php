<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
include 'conn.php';
$username="76b050f0-abce-11e9-a2f7-777d09356daa";
/*$conn = OpenCon();
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql = "SELECT `shipping_first_name`, `shipping_last_name`, `shipping_street_address_1`, `shipping_street_address_2`, `shipping_city`, `shipping_region`, `shipping_country`, `shipping_zipcode`, `shipping_phone`, `order_item_source`, `order_outsource_status`, `order_item_sku`,  `order_item_size`, `order_item_color`, `order_item_price`, `order_item_qty` FROM `order_outsource` WHERE 
srno=1";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
      
       $data=(object)array(
  'retailer'=> $row["order_item_source"],
  'products'=> array(
	(object)["product_id"=>  $row["order_item_sku"],"quantity"=>1]
    ),
  'max_price'=> 6000,
  'shipping_address'=> (object)array(
    "first_name"=> $row["shipping_first_name"],
    "last_name"=> $row["shipping_last_name"],
    "address_line1"=> $row["shipping_street_address_1"],
    "address_line2"=> $row["shipping_street_address_2"],
    "zip_code"=> $row["shipping_zipcode"],
    "city"=> $row["shipping_city"],
    "state"=> $row["shipping_region"],
    "country"=> $row["shipping_country"],
    "phone_number"=> $row["shipping_phone"]
  ),
 
  'shipping_method'=> 'free',
  "fbe"=> true,
  "webhooks"=> (object)array(
    "order_placed"=> "https://amazontest.requestcatcher.com/",
    "order_failed"=> "https://amazontest.requestcatcher.com/"
   
  )
);
  }
}*/
 $data=(object)array(
  'retailer'=> "amazon",
  'products'=> array((object)["product_id"=> "B01MUA0D2A","quantity"=>1,
	"seller_selection_criteria"=>(object)array(["prime"=> true]
	    )]
    ),
  'max_price'=> 6000,
  
  'shipping_address'=> (object)array(
    "first_name"=> "Jake",
    "last_name"=> "Moha",
    "address_line1"=> "30 north maple drive APT 207",
    "address_line2"=> "",
    "zip_code"=> "90210",
    "city"=> "beverly hills",
    "state"=> "CA",
    "country"=> "US",
    "phone_number"=> "9176220540"
  ),
 
  'shipping_method'=> 'free',
  "fbe"=> true,
  "webhooks"=> (object)array(
    "order_placed"=> "https://amazontest.requestcatcher.com/",
    "order_failed"=> "http://websoldeveloper.com/easync/orderconfirm.php"
   
  )
);
$data=json_encode($data);
print_r($data);

//echo "Starting Script \n";



//die();
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,"http://core.easync.io/api/v1/orders/");
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data))
);
$server_output = curl_exec($ch);
curl_close ($ch);
$server_output=json_decode($server_output);
print_r($server_output);
//print("<pre>".print_r(json_encode($server_output),true)."</pre>");
echo $server_output->request_id;
$conn = OpenCon();
$sql = "UPDATE `order_outsource` SET `Outsource_Request_Id`='".$server_output->request_id."' WHERE srno=1";

if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
$conn->close();
/* 'billing_address'=> (object)array(
    "first_name"=> "John",
    "last_name"=> "Smith",
    "address_line1"=> "14 Bus St.",
    "address_line2"=> "",
    "zip_code"=> "123456",
    "city"=> "Atlanta",
    "state"=> "GA",
    "country"=> "US",
    "phone_number"=> "1234567890"
  ),
  'payment_method'=>(object)array(
    "expiration_month"=> 9,
    "expiration_year"=> 9999,
    "name_on_card"=> "Jack Sparrow",
    "number"=> "0000000000000000",
    "security_code"=> "555",
    "use_gift"=> true
  ),
  
  'retailer_credentials'=> (object)array(
    "email"=> "jbbenmoha@gmail.com",
    "password"=> "PQThn()(909jV9Ag2B6yf"
  )*/
?>



