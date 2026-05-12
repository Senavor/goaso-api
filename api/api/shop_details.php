<?php

require "../db.php";
require "../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');

$secret_key = "YOUR_SUPER_SECRET_KEY";

/*
|--------------------------------------------------------------------------
| CHECK JWT TOKEN
|--------------------------------------------------------------------------
*/

$headers = getallheaders();

if(!isset($headers['Authorization'])){

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Token required"
    ]);

    exit;
}

$authHeader = $headers['Authorization'];

$arr = explode(" ", $authHeader);

$jwt = $arr[1];

try {

    $decoded = JWT::decode(
        $jwt,
        new Key($secret_key, 'HS256')
    );

} catch(Exception $e){

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Invalid token"
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| GET SHOP ID
|--------------------------------------------------------------------------
*/

if(!isset($_GET['id'])){

    echo json_encode([
        "success" => false,
        "message" => "Shop ID required"
    ]);

    exit;
}

$shop_id = (int)$_GET['id'];

/*
|--------------------------------------------------------------------------
| FETCH SHOP
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT *
FROM shops
WHERE id=?
");

$stmt->bind_param("i", $shop_id);

$stmt->execute();

$shop = $stmt->get_result()->fetch_assoc();

if(!$shop){

    echo json_encode([
        "success" => false,
        "message" => "Shop not found"
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| FETCH PHOTOS
|--------------------------------------------------------------------------
*/

$photos = [];

$photo_stmt = $conn->prepare("
SELECT photo_url
FROM shop_photos
WHERE shop_id=?
ORDER BY id DESC
");

$photo_stmt->bind_param("i", $shop_id);

$photo_stmt->execute();

$photo_result = $photo_stmt->get_result();

while($row = $photo_result->fetch_assoc()){
    $photos[] = $row;
}

/*
|--------------------------------------------------------------------------
| FETCH CATALOG
|--------------------------------------------------------------------------
*/

$catalog = [];

$catalog_stmt = $conn->prepare("
SELECT *
FROM shop_catalog
WHERE shop_id=?
ORDER BY created_at DESC
");

$catalog_stmt->bind_param("i", $shop_id);

$catalog_stmt->execute();

$catalog_result = $catalog_stmt->get_result();

while($row = $catalog_result->fetch_assoc()){
    $catalog[] = $row;
}

/*
|--------------------------------------------------------------------------
| RESPONSE
|--------------------------------------------------------------------------
*/

echo json_encode([
    "success" => true,

    "shop" => [
        "id" => $shop['id'],
        "name" => $shop['name'],
        "category" => $shop['category'],
        "address" => $shop['address'],
        "phone" => $shop['phone'],
        "logo" => $shop['logo'],
        "video" => $shop['video'],
        "status" => $shop['status']
    ],

    "photos" => $photos,

    "catalog" => $catalog
]);