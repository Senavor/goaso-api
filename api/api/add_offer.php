<?php

header('Content-Type: application/json');

require "../../db.php";

$shop_id =
    intval($_POST['shop_id'] ?? 0);

$title =
    trim($_POST['title'] ?? '');

$description =
    trim($_POST['description'] ?? '');

$price =
    floatval($_POST['price'] ?? 0);

$image = '';

if(isset($_FILES['image'])){

    $folder =
        "../../uploads/offers/";

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
        "https://goaso-api.onrender.com/uploads/offers/"
        . $fileName;
    }
}

$stmt = $conn->prepare("

INSERT INTO offers

(
shop_id,
title,
description,
price,
image
)

VALUES

(?,?,?,?,?)

");

$stmt->bind_param(

    "issds",

    $shop_id,
    $title,
    $description,
    $price,
    $image
);

if($stmt->execute()){

    echo json_encode([

        "success" => true,

        "message" =>
            "Offer added"
    ]);

} else {

    echo json_encode([

        "success" => false,

        "message" =>
            "Failed"
    ]);
}