<?php
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

// Validate required parameters
if (!isset($data['ambulance_id']) || !isset($data['latitude']) || !isset($data['longitude'])) {
    echo json_encode(["error" => "Missing required parameters"]);
    exit;
}

$ambulanceId = $data['ambulance_id'];
$latitude = $data['latitude'];
$longitude = $data['longitude'];
$status = isset($data['status']) ? $data['status'] : "on_route"; // Default status

// Data to update in Firebase
$firebaseData = json_encode([
    "latitude" => $latitude,
    "longitude" => $longitude,
    "status" => $status
]);

// Send data to Firebase
$options = [
    "http" => [
        "method" => "PATCH",
        "header" => "Content-Type: application/json",
        "content" => $firebaseData
    ]
];

$response = file_get_contents($firebase_url, false, stream_context_create($options));

if ($response !== false) {
    // Also update MySQL
    include("../config/db_config.php");
    $stmt = $conn->prepare("UPDATE ambulances SET latitude = ?, longitude = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ddsi", $latitude, $longitude, $status, $ambulanceId);
    
    if ($stmt->execute()) {
        echo json_encode(["message" => "Location updated in Firebase and MySQL"]);
    } else {
        echo json_encode(["error" => "Failed to update MySQL"]);
    }
} else {
    echo json_encode(["error" => "Failed to update Firebase"]);
}
?>
<?php
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

// Validate required parameters
if (!isset($data['ambulance_id']) || !isset($data['latitude']) || !isset($data['longitude'])) {
    echo json_encode(["error" => "Missing required parameters"]);
    exit;
}

$ambulanceId = $data['ambulance_id'];
$latitude = $data['latitude'];
$longitude = $data['longitude'];
$status = isset($data['status']) ? $data['status'] : "on_route"; // Default status

$firebaseDatabaseURL = "https://healthcare-b88c6-default-rtdb.firebaseio.com";
$firebaseSecret = "UTKRgy4zx6Ul2WS4LIqXiOINiuyiu0Foys2bZ9yA";
// Firebase URL
$firebase_url = "$firebaseDatabaseURL/ambulances/$ambulanceId.json?auth=$firebaseSecret";

// Data to update in Firebase
$firebaseData = json_encode([
    "latitude" => $latitude,
    "longitude" => $longitude,
    "status" => $status
]);

// Send PATCH request to Firebase
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $firebase_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH"); // ✅ Use PATCH
curl_setopt($ch, CURLOPT_POSTFIELDS, $firebaseData);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Check if Firebase update was successful
if ($http_code >= 200 && $http_code < 300) {
    // ✅ Firebase updated successfully, now update MySQL
    include("../config/db_config.php");

    $stmt = $conn->prepare("UPDATE ambulances SET latitude = ?, longitude = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ddsi", $latitude, $longitude, $status, $ambulanceId);
    
    if ($stmt->execute()) {
        echo json_encode(["message" => "Location updated in Firebase and MySQL"]);
    } else {
        echo json_encode(["error" => "Failed to update MySQL"]);
    }
} else {
    echo json_encode(["error" => "Failed to update Firebase", "firebase_response" => $response]);
}
?>
