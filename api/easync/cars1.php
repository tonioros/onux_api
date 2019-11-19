<?php
require('../simple_html_dom.php');
include 'conn.php';
$conn = OpenCon();
       
   //   header("Content-type: text/csv");       
     // header("Content-Disposition: attachment; filename=$filename");
    // $output = fopen("userData.csv", "a");

 for($j=2;$j<=18337;$j++){       
		$ch = curl_init();
		$url="https://deals.jumia.ci/voitures?page=".$j;
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");
$html = curl_exec($ch);
echo $url."</br>";
curl_close($ch);
$dom = new DOMDocument();
@$dom->loadHTML($html);
$dom->preserveWhiteSpace = false;
$xpath = new DOMXPath($dom);

       
       	$currency = $xpath->evaluate(
    "/html/body//a[@class='post-link post-vip']"
);

for($i=0;$i<sizeof($currency);$i++){
$sql = "insert into `url1` (`url`) values('https://deals.jumia.ci".$currency->item($i)->getAttribute("href")."')";

//echo $currency->item($i)->getAttribute("href")."</br>";
if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
}


}

$conn->close();
?>



