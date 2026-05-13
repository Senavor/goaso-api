<?php

header('Content-Type: application/json');

require "../../db.php";

/*
|--------------------------------------------------------------------------
| VALIDATE LOGIN
|--------------------------------------------------------------------------
*/

$owner_id = trim($_POST['owner_id'] ?? '');

if(!$owner_id){

    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| GET INPUTS
|--------------------------------------------------------------------------
*/

$name       = trim($_POST['name'] ?? '');
$category   = trim($_POST['category'] ?? '');
$address    = trim($_POST['address'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$email      = trim($_POST['email'] ?? '');
$whatsapp   = trim($_POST['whatsapp'] ?? '');
$open_hours = trim($_POST['open_hours'] ?? '');
$map_link   = trim($_POST['map_link'] ?? '');
$pin_code   = trim($_POST['pin_code'] ?? '');

/*
|--------------------------------------------------------------------------
| VALIDATION
|--------------------------------------------------------------------------
*/

if(
    !$name ||
    !$category ||
    !$address ||
    !$phone
){

    echo json_encode([
        "success" => false,
        "message" => "Required fields missing"
    ]);

    exit;
}

if(!preg_match('/^[0-9]{4,6}$/', $pin_code)){

    echo json_encode([
        "success" => false,
        "message" => "PIN must be 4-6 digits"
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| HASH PIN
|--------------------------------------------------------------------------
*/

$pin_hash = password_hash(
    $pin_code,
    PASSWORD_DEFAULT
);

/*
|--------------------------------------------------------------------------
| IMAGE UPLOAD
|--------------------------------------------------------------------------
*/

$logo = '';

if(isset($_FILES['logo'])){

    $uploadDir =
        "../../uploads/";

    if(!is_dir($uploadDir)){

        mkdir(
            $uploadDir,
            0777,
            true
        );
    }

    $fileName =
        time() . '_' .
        basename(
            $_FILES['logo']['name']
        );

    $target =
        $uploadDir . $fileName;

    if(move_uploaded_file(

        $_FILES['logo']['tmp_name'],

        $target

    )){

        $logo =
        "https://goaso-api.onrender.com/uploads/"
        . $fileName;
    }
}

/*
|--------------------------------------------------------------------------
| VIDEO UPLOAD
|--------------------------------------------------------------------------
*/

$video = '';

if(isset($_FILES['shop_video'])){

    $videoDir =
        "../../uploads/videos/";

    if(!is_dir($videoDir)){

        mkdir(
            $videoDir,
            0777,
            true
        );
    }

    $videoName =
        time() . '_' .
        basename(
            $_FILES['shop_video']['name']
        );

    $videoTarget =
        $videoDir . $videoName;

    if(move_uploaded_file(

        $_FILES['shop_video']['tmp_name'],

        $videoTarget

    )){

        $video =
        "https://goaso-api.onrender.com/uploads/videos/"
        . $videoName;
    }
}

/*
|--------------------------------------------------------------------------
| INSERT SHOP
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("

INSERT INTO shops

(
    owner_id,
    name,
    category,
    address,
    phone,
    email,
    whatsapp,
    open_hours,
    map_link,
    logo,
    video,
    pin_hash
)

VALUES

(?,?,?,?,?,?,?,?,?,?,?,?)

");

$stmt->bind_param(

    "isssssssssss",

    $owner_id,
    $name,
    $category,
    $address,
    $phone,
    $email,
    $whatsapp,
    $open_hours,
    $map_link,
    $logo,
    $video,
    $pin_hash
);

if($stmt->execute()){

    $shop_id =
        $stmt->insert_id;

    /*
    |--------------------------------------------------------------------------
    | FREE TRIAL
    |--------------------------------------------------------------------------
    */

    $start_date =
        date('Y-m-d');

    $end_date =
        date(
            'Y-m-d',
            strtotime('+3 months')
        );

    $sub = $conn->prepare("

    INSERT INTO subscriptions

    (
        shop_id,
        plan_name,
        amount,
        start_date,
        end_date,
        subscription_status,
        type
    )

    VALUES

    (?, 'Trial Plan', 0, ?, ?, 'active', 'trial')

    ");

    $sub->bind_param(
        "iss",
        $shop_id,
        $start_date,
        $end_date
    );

    $sub->execute();

    echo json_encode([

        "success" => true,

        "message" =>
            "Shop added successfully",

        "shop_id" => $shop_id,

        "logo" => $logo,

        "video" => $video
    ]);

} else {

    echo json_encode([

        "success" => false,

        "message" =>
            "Failed to add shop",

        "error" =>
            $conn->error
    ]);
}