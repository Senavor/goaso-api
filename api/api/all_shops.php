<?php
require_once "../../db.php";

header('Content-Type: application/json');

$sql = "
SELECT 
    c.id,
    c.shop_id,
    c.media_type,
    c.media_url,
    c.description,
    s.name AS shop_name,
    s.category AS shop_category,
    s.phone AS shop_phone
FROM shop_catalog c
JOIN shops s ON c.shop_id = s.id
WHERE s.status='approved'
ORDER BY c.created_at DESC
";

$result = $conn->query($sql);

$shops = [];

while($row = $result->fetch_assoc()){
    $shops[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $shops
]);