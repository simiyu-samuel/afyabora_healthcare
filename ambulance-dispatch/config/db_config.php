<?php
// MySQL Database connection configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ambulance_dispatch";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
