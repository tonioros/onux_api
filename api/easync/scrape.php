<?php
require('../simple_html_dom.php');
include 'conn.php';
$conn = OpenCon();

       
   //   header("Content-type: text/csv");       
     // header("Content-Disposition: attachment; filename=$filename");
    // $output = fopen("userData.csv", "a");
$sql = "SELECT url from url";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
      
        
		$ch = curl_init();
		$url=$row["url"];
		echo $url;
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");
$html = curl_exec($ch);
curl_close($ch);
		
$dom = new DOMDocument();
@$dom->loadHTML($html);
$dom->preserveWhiteSpace = false;
$xpath = new DOMXPath($dom);
$hrefs = $xpath->evaluate(
    "/html/body//h1[@class='title fsize-24 lh-30']"
);
//echo $hrefs->item(0)->textContent."</br>";

//	echo $html;
	$Header = $xpath->evaluate(
    "/html/body//span[@class='value']"
);


       
       	$location = $xpath->evaluate(
    "/html/body//div[@class='location']"
);

//echo $location->item(0)->textContent."</br>";

    	$price = $xpath->evaluate(
    "/html/body//div[@class='price pull-left']"
);
//echo $price->item(0)->textContent."</br>";
	$specs = $xpath->evaluate(
    "/html/body//div[@class='value']"
);


$image = $xpath->evaluate(
    "/html/body//img[@rel='imagegallery-0']"
);
//echo $image->item(0)->getAttribute("src")."</br>";

	$date = $xpath->evaluate(
    "/html/body//span[@class='pull-left']"
);



try {
   if(sizeof($image)>0){
 $sql = "INSERT INTO `data`(`Name`, `Make`, `Model`, `Year`, `City`, `Price`, `Color`, `Transmission`, `Mileage`, `Posting_url`, `website_url`, `Country`, `Image`, `Publish`) VALUES ('".$hrefs->item(0)->textContent."','".$Header->item(0)->textContent."','".$Header->item(1)->textContent."','".$Header->item(3)->textContent."','".$location->item(0)->textContent."','".$price->item(0)->textContent."','".$specs->item(4)->textContent."','".$specs->item(2)->textContent."','".$specs->item(1)->textContent."','".$url."','https://kupatana.com/buy-and-sell-cars-tanzania
','Tanzania
','".$image->item(0)->getAttribute("src")."','".$date->item(1)->textContent."')";
//echo $currency->item($i)->getAttribute("href")."</br>";
if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
   
}
}
}

//catch exception
catch(Exception $e) {
  echo 'Message: ' .$e->getMessage();
  continue;
}



}

}
$conn->close();
?>