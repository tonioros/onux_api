<?php 
    include_once 'main.inc.php';
    
        global $lang_label, $obj, $tconfig, $generalobj;
    
        $returnArr = array();
    
        $counter = 0;
        for ($i = 0; $i < 26; $i++) {
            $cahracter = chr(65 + $i);
    
            $sql = "SELECT COU.* FROM country as COU WHERE COU.eStatus = 'Active' AND COU.vPhoneCode!='' AND COU.vCountryCode!='' AND COU.vCountry LIKE '$cahracter%' ORDER BY COU.vCountry";
            $db_rec = $obj->MySQLSelect($sql);
    
            if (count($db_rec) > 0) {
    
                $countryListArr = array();
                $subCounter = 0;
                for ($j = 0; $j < count($db_rec); $j++) {
    
                    $countryListArr[$subCounter] = $db_rec[$j];
                    $subCounter++;
                }
    
                if (count($countryListArr) > 0) {
                    $returnArr[$counter]['key'] = $cahracter;
                    $returnArr[$counter]['TotalCount'] = count($countryListArr);
                    $returnArr[$counter]['List'] = $countryListArr;
    
                    $counter++;
    
                }
            }
    
        }
    
        $countryArr['Action'] = "1";
        $countryArr['totalValues'] = count($returnArr);
        $countryArr['CountryList'] = $returnArr;
        echo json_encode($countryArr);
        exit;
?>