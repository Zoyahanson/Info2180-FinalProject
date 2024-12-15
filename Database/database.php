<?php
$servername = "localhost";
$username = "admin@project2.com";
$password = "password123";
$dbname = "dolphin_crm";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
