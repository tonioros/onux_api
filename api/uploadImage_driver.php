<?php 

    $target_path = "webimages/upload/";
    $user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
    $base = isset($_REQUEST['image']) ? $_REQUEST['image'] : '';
    $name = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : '';
    $target_path_temp = $target_path . "Driver/";
    $target_path = $target_path_temp . $user_id . "/";

    if (is_dir($target_path) === false) {
        mkdir($target_path, 0755);
    }
    // base64 encoded utf-8 string
    $binary = base64_decode($base);

    header('Content-Type: bitmap; charset=utf-8');

    $time_val = time();
    $img_arr = explode(".", $name);
    $fileextension = $img_arr[count($img_arr) - 1];

    $Random_filename = mt_rand(11111, 99999);
    // $ImgFileName="3_".$name;
    $ImgFileName = $time_val . "_" . $Random_filename . "." . $fileextension;

    $file = fopen($target_path . '/' . $ImgFileName, "w");

    fwrite($file, $binary);
    fclose($file);

    $path = $target_path . $ImgFileName;

    if (file_exists($path)) {

        $where = " iDriverId = '" . $user_id . "'";
        $Data_Driver['vImage'] = $ImgFileName;
        $id = $obj->MySQLQueryPerform("register_driver", $Data_Driver, 'update', $where);

        if ($id > 0) {
            // echo "UPLOADSUCCESS";
            $thumb->createthumbnail($target_path . '/' . $ImgFileName); // generate image_file, set filename to resize/resample
            $thumb->size_auto($tconfig["tsite_upload_images_member_size1"]); // set the biggest width or height for thumbnail
            $thumb->jpeg_quality(100);
            $thumb->save($target_path . "1" . "_" . $time_val . "_" . $Random_filename . "." . $fileextension);

            $thumb->createthumbnail($target_path . "/" . $ImgFileName); // generate image_file, set filename to resize/resample
            $thumb->size_auto($tconfig["tsite_upload_images_member_size2"]); // set the biggest width or height for thumbnail
            $thumb->jpeg_quality(100); // [OPTIONAL] set quality for jpeg only (0 - 100) (worst - best), default = 75
            $thumb->save($target_path . "2" . "_" . $time_val . "_" . $Random_filename . "." . $fileextension);

            $thumb->createthumbnail($target_path . "/" . $ImgFileName); // generate image_file, set filename to resize/resample
            $thumb->size_auto($tconfig["tsite_upload_images_member_size3"]); // set the biggest width or height for thumbnail
            $thumb->jpeg_quality(100); // [OPTIONAL] set quality for jpeg only (0 - 100) (worst - best), default = 75
            $thumb->save($target_path . "3" . "_" . $time_val . "_" . $Random_filename . "." . $fileextension);

            $returnArrayImg['Action'] = "SUCCESS";
            $returnArrayImg['ImgName'] = '3_' . $ImgFileName;
            echo json_encode($returnArrayImg);
        } else {
            echo "Failed";
        }

    } else {
        // handle the error

        echo "Failed";
    }

    exit;

?>