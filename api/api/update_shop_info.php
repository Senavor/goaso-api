<?php

header('Content-Type: application/json');

require "../../db.php";

/*
|--------------------------------------------------------------------------
| INPUTS
|--------------------------------------------------------------------------
*/

$shop_id =
    intval($_POST['shop_id'] ?? 0);

$address =
    trim($_POST['address'] ?? '');

$whatsapp =
    trim($_POST['whatsapp'] ?? '');

$open_hours =
    trim($_POST['open_hours'] ?? '');

$map_link =
    trim($_POST['map_link'] ?? '');

/*
|--------------------------------------------------------------------------
| VALIDATION
|--------------------------------------------------------------------------
*/

if(!$shop_id){

    echo json_encode([

        "success" => false,

        "message" =>
            "Invalid shop"
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| CURRENT SHOP
|--------------------------------------------------------------------------
*/

$shop = $conn->query("

SELECT logo, video

FROM shops

WHERE id = $shop_id

LIMIT 1

")->fetch_assoc();

$logo =
    $shop['logo'] ?? '';

$video =
    $shop['video'] ?? '';

/*
|--------------------------------------------------------------------------
| LOGO UPLOAD
|--------------------------------------------------------------------------
*/

if(isset($_FILES['logo'])){

    $folder =
        "../../uploads/";

    if(!is_dir($folder)){

        mkdir(
            $folder,
            0777,
            true
        );
    }

    $fileName =

        time() . '_' .

        basename(
            $_FILES['logo']['name']
        );

    $target =
        $folder . $fileName;

    if(move_uploaded_file(

        $_FILES['logo']['tmp_name'],

        $target

    )){

        $logo =
        "https://goaso-api.onrender.com/uploads/"
        . $fileName;
    }
}

/*
|--------------------------------------------------------------------------
| VIDEO UPLOAD
|--------------------------------------------------------------------------
*/

if(isset($_FILES['shop_video'])){

    $folder =
        "../../uploads/videos/";

    if(!is_dir($folder)){

        mkdir(
            $folder,
            0777,
            true
        );
    }

    $fileName =

        time() . '_' .

        basename(
            $_FILES['shop_video']['name']
        );

    $target =
        $folder . $fileName;

    if(move_uploaded_file(

        $_FILES['shop_video']['tmp_name'],

        $target

    )){

        $video =
        "https://goaso-api.onrender.com/uploads/videos/"
        . $fileName;
    }
}

/*
|--------------------------------------------------------------------------
| UPDATE
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("

UPDATE shops

SET

address=?,
whatsapp=?,
open_hours=?,
map_link=?,
logo=?,
video=?

WHERE id=?

");

$stmt->bind_param(

    "ssssssi",

    $address,
    $whatsapp,
    $open_hours,
    $map_link,
    $logo,
    $video,
    $shop_id
);

if($stmt->execute()){

    echo json_encode([

        "success" => true,

        "message" =>
            "Shop updated",

        "logo" =>
            $logo,

        "video" =>
            $video
    ]);

} else {

    echo json_encode([

        "success" => false,

        "message" =>
            "Update failed"
    ]);
}