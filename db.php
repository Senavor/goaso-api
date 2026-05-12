<?php

$host = "yamabiko.proxy.rlwy.net";
$port = 28539;
$user = "root";
$password = "iuVtfgNbpWzSSRFGEKsTePVXMgelgUek";
$database = "railway";

$conn = new mysqli(
    $host,
    $user,
    $password,
    $database,
    $port
);

if ($conn->connect_error) {
    die("Connection failed: " .
        $conn->connect_error);
}
?>