<?php
header("Content-Type: application/json");
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../config/db_config.php");
include("../config/main_db.php");

// Read JSON input from the request
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['latitude']) || !isset($data['longitude'])) {
    die(json_encode(["error" => "Invalid request data"]));
}

$userLat = $conn->real_escape_string($data['latitude']);
$userLon = $conn->real_escape_string($data['longitude']);

// SQL query to find the nearest available ambulance
$sql = "SELECT id, latitude, longitude, driver_phone, 
               (6371 * acos(cos(radians($userLat)) * cos(radians(latitude)) * 
               cos(radians(longitude) - radians($userLon)) + sin(radians($userLat)) * sin(radians(latitude)))) 
               AS distance
        FROM ambulances WHERE status = 'available' ORDER BY distance ASC LIMIT 1";

$result = $conn->query($sql);

if (!$result) {
    die(json_encode(["error" => "SQL Error: " . $conn->error]));
}

$ambulance = $result->fetch_assoc();



if ($ambulance) {
    $ambulanceId = $ambulance['id'];
    $driverPhone = $ambulance['driver_phone'];
    // Update ambulance status to 'busy'
    $updateQuery = "UPDATE ambulances SET status = 'busy' WHERE id = $ambulanceId";
    if (!$conn->query($updateQuery)) {
        die(json_encode(["error" => "Failed to update ambulance status: " . $conn->error]));
    }

    // Firebase Database URL & Secret
    $firebaseDatabaseURL = "https://healthcare-b88c6-default-rtdb.firebaseio.com";
    $firebaseSecret = "UTKRgy4zx6Ul2WS4LIqXiOINiuyiu0Foys2bZ9yA";

    $patientId = $_SESSION['pid'];
    // Firebase Data Update
    $firebaseData = json_encode([
        "latitude" => $ambulance['latitude'],
        "longitude" => $ambulance['longitude'],
        "status" => 'on_way',
        "patientId" => $patientId,
        "patientLat" => $userLat,
        "patientLog" => $userLon
    ]);

    $firebaseURL = "$firebaseDatabaseURL/ambulances/$ambulanceId.json?auth=$firebaseSecret";
    $firebaseContext = stream_context_create([
        "http" => [
            "method" => "PUT",
            "header" => "Content-Type: application/json",
            "content" => $firebaseData
        ]
    ]);

    $firebaseResponse = file_get_contents($firebaseURL, false, $firebaseContext);

    // Debug Firebase response
    if (!$firebaseResponse) {
        die(json_encode(["error" => "Failed to update Firebase"]));
    }

    echo json_encode([
        "ambulance_id" => $ambulanceId,
        "driverPhone" => $driverPhone,
        "message" => "Ambulance dispatched successfully."
    ]);
} else {
    echo json_encode(["message" => "No available ambulance."]);
}
?>
