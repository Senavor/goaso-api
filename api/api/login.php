<?php
require "../db.php";
require "../vendor/autoload.php";
header('Content-Type: application/json');

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if(!$email || !$password){
    echo json_encode([
        "success" => false,
        "message" => "All fields required"
    ]);
    exit;
}

$stmt = $conn->prepare("
SELECT id, name, password, avatar, role 
FROM user 
WHERE email=?
");

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result()->fetch_assoc();

if($result && password_verify($password, $result['password'])){

    echo json_encode([
        "success" => true,
        "user" => [
            "id" => $result['id'],
            "name" => $result['name'],
            "avatar" => $result['avatar'],
            "role" => $result['role']
        ]
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Invalid credentials"
    ]);

}
?>