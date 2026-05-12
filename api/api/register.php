<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require "../../db.php";

/*
|--------------------------------------------------------------------------
| GET INPUTS
|--------------------------------------------------------------------------
*/

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

/*
|--------------------------------------------------------------------------
| VALIDATION
|--------------------------------------------------------------------------
*/

if(!$name || !$email || !$password){

    echo json_encode([
        "success" => false,
        "message" => "All fields are required"
    ]);

    exit;
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){

    echo json_encode([
        "success" => false,
        "message" => "Invalid email"
    ]);

    exit;
}

if(strlen($password) < 6){

    echo json_encode([
        "success" => false,
        "message" => "Password must be at least 6 characters"
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| CHECK IF EMAIL EXISTS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT id
FROM user
WHERE email=?
");

$stmt->bind_param("s", $email);

$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows > 0){

    echo json_encode([
        "success" => false,
        "message" => "Email already exists"
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| CREATE ACCOUNT
|--------------------------------------------------------------------------
*/

$hashed_password = password_hash(
    $password,
    PASSWORD_DEFAULT
);

$default_avatar = "uploads/default-avatar.png";

$insert = $conn->prepare("
INSERT INTO user
(name, email, password, avatar)
VALUES (?, ?, ?, ?)
");

$insert->bind_param(
    "ssss",
    $name,
    $email,
    $hashed_password,
    $default_avatar
);

if(!$insert->execute()){

    echo json_encode([
        "success" => false,
        "message" => "Registration failed",
        "error" => $insert->error
    ]);

    exit;
}

$user_id = $insert->insert_id;

/*
|--------------------------------------------------------------------------
| RESPONSE
|--------------------------------------------------------------------------
*/

echo json_encode([

    "success" => true,

    "message" => "Account created successfully",

    "token" => "temporary_token",

    "user" => [

        "id" => $user_id,

        "name" => $name,

        "email" => $email,

        "avatar" => $default_avatar
    ]
]);