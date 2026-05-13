<?php

header('Content-Type: application/json');

require "../../db.php";

$shop_id =
    intval($_POST['shop_id'] ?? 0);

$is_open =
    intval($_POST['is_open'] ?? 0);

if(!$shop_id){

    echo json_encode([

        "success" => false,

        "message" =>
            "Invalid shop"
    ]);

    exit;
}

$stmt = $conn->prepare("

UPDATE shops

SET is_open=?

WHERE id=?

");

$stmt->bind_param(

    "ii",

    $is_open,

    $shop_id
);

if($stmt->execute()){

    echo json_encode([

        "success" => true,

        "message" =>
            "Status updated"
    ]);

} else {

    echo json_encode([

        "success" => false,

        "message" =>
            "Failed"
    ]);
}