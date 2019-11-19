<?php
require('../simple_html_dom.php');
include 'conn.php';
$conn = OpenCon();

       
   //   header("Content-type: text/csv");       
     // header("Content-Disposition: attachment; filename=$filename");
    // $output = fopen("userData.csv", "a");
$sql = "SELECT Distinict(url) from url1";
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
    "/html/body//h1/span[@itemprop='name']"
);
echo $hrefs->item(0)->textContent."</br>";

//	echo $html;
	$Header = $xpath->evaluate(
    "/html/body//div[@class='new-attr-style']/h3/span"
);

foreach($Header as $item)
{
    echo $item->textContent."</br>";
}  

       	$location = $xpath->evaluate(
    "/html/body//span[@itemprop='locality']"
);

echo $location->item(0)->textContent."</br>";

    	$price = $xpath->evaluate(
    "/html/body//span[@itemprop='price']"
);
$pricec  = $xpath->evaluate(
    "/html/body//span[@itemprop='priceCurrency']"
);
$cprice=$price->item(0)->textContent."".$pricec->item(0)->textContent;
echo $cprice."</br>";
$image = $xpath->evaluate(
    "/html/body//img[@itemprop='image']"
);

echo $image->item(0)->getAttribute("data-src")."</br>";

	$date = $xpath->evaluate(
    "/html/body//time"
);
echo $date->item(0)->getAttribute("datetime")."</br>";



try {
   if(sizeof($image)>0){
 $sql = "INSERT INTO `data`(`Name`, `Make`, `Model`, `Year`, `City`, `Price`, `Color`, `Transmission`, `Mileage`, `Posting_url`, `website_url`, `Country`, `Image`, `Publish`) VALUES ('".$hrefs->item(0)->textContent."','".$Header->item(0)->textContent."','".$Header->item(1)->textContent."','','".$location->item(0)->textContent."','".$cprice."','','".$Header->item(2)->textContent."','".$Header->item(5)->textContent."','".$url."','https://deals.jumia.ci/','Ivory Coast
','".$image->item(0)->getAttribute("data-src")."','".$date->item(0)->getAttribute("datetime")."')";
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
  //continue;
}



//}

//}
$conn->close();
?>