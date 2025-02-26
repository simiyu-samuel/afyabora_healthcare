<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $symptoms = $_POST['symptoms'];

    $data = json_encode(["symptoms" => explode(",", $symptoms)]);
    $ch = curl_init("http://127.0.0.1:5000/predict");

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    echo "Diagnosis: " . $result['diagnosis'];
}
?>
