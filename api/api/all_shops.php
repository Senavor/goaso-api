<?php

header('Content-Type: application/json');

require "../../db.php";

$result = $conn->query("
SELECT *
FROM shops
ORDER BY id DESC
");

$shops = [];

while($row = $result->fetch_assoc()){

    $shops[] = $row;
}

echo json_encode([

    "success" => true,

    "shops" => $shops
]);