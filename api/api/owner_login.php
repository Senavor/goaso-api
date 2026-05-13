<?php

header('Content-Type: application/json');

require "../../db.php";

/*
|--------------------------------------------------------------------------
| GET INPUTS
|--------------------------------------------------------------------------
*/

$shop_name = trim(
    $_POST['shop_name'] ?? ''
);

$phone = trim(
    $_POST['phone'] ?? ''
);

$pin = trim(
    $_POST['pin_code'] ?? ''
);

/*
|--------------------------------------------------------------------------
| VALIDATION
|--------------------------------------------------------------------------
*/

if(
    !$shop_name ||
    !$phone ||
    !$pin
){

    echo json_encode([

        "success" => false,

        "message" =>
            "All fields are required"
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| CLEAN PHONE
|--------------------------------------------------------------------------
*/

$phone = str_replace(

    [" ", "-", "+"],

    "",

    $phone
);

/*
|--------------------------------------------------------------------------
| FIND SHOP
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("

SELECT

    id,
    name,
    phone,

    IFNULL(pin_hash, '') AS pin_hash,

    IFNULL(pin_code, '') AS pin_code,

    logo,
    category,
    address

FROM shops

WHERE LOWER(name)=LOWER(?)

AND REPLACE(

    REPLACE(

        REPLACE(phone,'+',''),

    '-',''),

' ','')

= ?

LIMIT 1

");

$stmt->bind_param(

    "ss",

    $shop_name,

    $phone
);

$stmt->execute();

$result =
    $stmt->get_result();

/*
|--------------------------------------------------------------------------
| CHECK SHOP
|--------------------------------------------------------------------------
*/

if(
    !$result ||

    $result->num_rows === 0
){

    echo json_encode([

        "success" => false,

        "message" =>
            "No matching shop found"
    ]);

    exit;
}

$shop =
    $result->fetch_assoc();

/*
|--------------------------------------------------------------------------
| VERIFY PIN
|--------------------------------------------------------------------------
*/

$stored_hash =

    $shop['pin_hash']

    ?:

    $shop['pin_code'];

if(

    !$stored_hash ||

    !password_verify(
        $pin,
        $stored_hash
    )

){

    echo json_encode([

        "success" => false,

        "message" =>
            "Incorrect PIN"
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| SUCCESS
|--------------------------------------------------------------------------
*/

echo json_encode([

    "success" => true,

    "message" =>
        "Login successful",

    "shop" => [

        "id" =>
            $shop['id'],

        "name" =>
            $shop['name'],

        "phone" =>
            $shop['phone'],

        "logo" =>
            $shop['logo'],

        "category" =>
            $shop['category'],

        "address" =>
            $shop['address']
    ]
]);