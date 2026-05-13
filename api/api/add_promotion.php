<?php

header('Content-Type: application/json');

require "../../db.php";

$shop_id =
    intval($_POST['shop_id'] ?? 0);

$title =
    trim($_POST['title'] ?? '');

$description =
    trim($_POST['description'] ?? '');

$start_date =
    trim($_POST['start_date'] ?? '');

$end_date =
    trim($_POST['end_date'] ?? '');

$image = '';

if(isset($_FILES['image'])){

    $folder =
        "../../uploads/promotions/";

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
            $_FILES['image']['name']
        );

    $target =
        $folder . $fileName;

    if(move_uploaded_file(

        $_FILES['image']['tmp_name'],

        $target

    )){

        $image =
        "https://goaso-api.onrender.com/uploads/promotions/"
        . $fileName;
    }
}

$stmt = $conn->prepare("

INSERT INTO promotions

(
shop_id,
title,
description,
image,
start_date,
end_date
)

VALUES

(?,?,?,?,?,?)

");

$stmt->bind_param(

    "isssss",

    $shop_id,
    $title,
    $description,
    $image,
    $start_date,
    $end_date
);

if($stmt->execute()){

    echo json_encode([

        "success" => true,

        "message" =>
            "Promotion added"
    ]);

} else {

    echo json_encode([

        "success" => false,

        "message" =>
            "Failed"
    ]);
}