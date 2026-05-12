<?php
// Database connection settings
$host = "sql102.infinityfree.com"; 
$user = "if0_40232781";        
$pass = "NobleGsf1223";            
$db   = "if0_40232781_goasodb"; 

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

date_default_timezone_set('Africa/Accra');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
