<?php 
    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? trim($_REQUEST['iVehicleCategoryId']) : 0;

    $languageCode = "";
    if ($iDriverId != "") {
        $languageCode = get_value('register_driver', 'vLang', 'iDriverId', $iDriverId, '', 'true');
    }

    if ($languageCode == "" || $languageCode == null) {
        $languageCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $ssql_category = "";
    $returnName = "vTitle";
    if ($iVehicleCategoryId != "" && ($iVehicleCategoryId == 0 || $iVehicleCategoryId == "0")) {
        $ssql_category = " and (select count(iVehicleCategoryId) from vehicle_category where iParentId=vc.iVehicleCategoryId AND eStatus='Active') > 0";
        $returnName = "vCategory";
    }

    $per_page = 10;
    $sql_all = "SELECT COUNT(iVehicleCategoryId) As TotalIds FROM vehicle_category as vc WHERE vc.eStatus='Active' AND vc.iParentId='" . $iVehicleCategoryId . "'" . $ssql_category;
    $data_count_all = $obj->MySQLSelect($sql_all);
    $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);

    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;

    $sql = "SELECT vc.iVehicleCategoryId, vc.vCategory_" . $languageCode . " as '" . $returnName . "' FROM vehicle_category as vc WHERE vc.eStatus='Active' AND vc.iParentId='" . $iVehicleCategoryId . "'" . $ssql_category . $limit;
    $vehicleCategoryDetail = $obj->MySQLSelect($sql);

    $vehicleCategoryData = array();

    if (count($vehicleCategoryDetail) > 0) {
        $vehicleCategoryData = $vehicleCategoryDetail;
        if ($iVehicleCategoryId != "" && ($iVehicleCategoryId == 0 || $iVehicleCategoryId == "0")) {
            $i = 0;
            while (count($vehicleCategoryDetail) > $i) {

                $iVehicleCategoryId = $vehicleCategoryDetail[$i]['iVehicleCategoryId'];

                $sql = "SELECT vCategory_" . $languageCode . " as vTitle,iVehicleCategoryId FROM `vehicle_category` WHERE iParentId='" . $iVehicleCategoryId . "' AND eStatus='Active'";
                $subCategoryData = $obj->MySQLSelect($sql);

                $vehicleCategoryData[$i]['SubCategory'] = $subCategoryData;
                $i++;
            }
        }

        $returnArr['Action'] = "1";
        if ($TotalPages > $page) {
            $returnArr['NextPage'] = "" . ($page + 1);
        } else {
            $returnArr['NextPage'] = "0";
        }
        $returnArr['message'] = $vehicleCategoryData;

    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DATA_AVAIL";

    }
    echo json_encode($returnArr);



?>