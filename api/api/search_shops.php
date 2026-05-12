<?php

require "../../db.php";
require "../../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');

$secret_key = "YOUR_SUPER_SECRET_KEY";

/*
|--------------------------------------------------------------------------
| VERIFY JWT TOKEN
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

    JWT::decode(
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
| GET FILTERS
|--------------------------------------------------------------------------
*/

$query = trim($_GET['query'] ?? '');

$category = trim($_GET['category'] ?? '');

$page = isset($_GET['page'])
    ? (int)$_GET['page']
    : 1;

$limit = 20;

$offset = ($page - 1) * $limit;

/*
|--------------------------------------------------------------------------
| BASE QUERY
|--------------------------------------------------------------------------
*/

$sql = "

SELECT sh.*

FROM shops sh

JOIN subscriptions s
ON sh.id = s.shop_id

WHERE sh.status='approved'

AND s.end_date >= CURDATE()

AND s.subscription_status
IN ('trial','active')

";

/*
|--------------------------------------------------------------------------
| SEARCH FILTERS
|--------------------------------------------------------------------------
*/

$params = [];

$types = "";

if($query !== ''){

    $sql .= "
    AND (
        sh.name LIKE ?
        OR sh.category LIKE ?
        OR sh.address LIKE ?
    )
    ";

    $search = "%$query%";

    $params[] = $search;
    $params[] = $search;
    $params[] = $search;

    $types .= "sss";
}

if($category !== ''){

    $sql .= " AND sh.category=? ";

    $params[] = $category;

    $types .= "s";
}

/*
|--------------------------------------------------------------------------
| PAGINATION
|--------------------------------------------------------------------------
*/

$sql .= "
ORDER BY sh.created_at DESC
LIMIT ?
OFFSET ?
";

$params[] = $limit;
$params[] = $offset;

$types .= "ii";

/*
|--------------------------------------------------------------------------
| EXECUTE
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare($sql);

$stmt->bind_param($types, ...$params);

$stmt->execute();

$result = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| FORMAT DATA
|--------------------------------------------------------------------------
*/

$shops = [];

while($row = $result->fetch_assoc()){

    $shops[] = [

        "id" => $row['id'],

        "name" => $row['name'],

        "address" => $row['address'],

        "category" => $row['category'],

        "phone" => $row['phone'],

        "whatsapp" => $row['whatsapp'],

        "logo" => $row['logo'],

        "video" => $row['video'],

        "open_hours" => $row['open_hours'],

        "map_link" => $row['map_link']
    ];
}

/*
|--------------------------------------------------------------------------
| RESPONSE
|--------------------------------------------------------------------------
*/

echo json_encode([

    "success" => true,

    "page" => $page,

    "shops" => $shops
]);