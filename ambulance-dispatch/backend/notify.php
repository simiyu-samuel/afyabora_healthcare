<?php
// Include Twilio SDK
require_once 'vendor/autoload.php';
use Twilio\Rest\Client;

header("Content-Type: application/json");

$account_sid = "YOUR_TWILIO_ACCOUNT_SID";
$auth_token = "YOUR_TWILIO_AUTH_TOKEN";
$from_number = "YOUR_TWILIO_PHONE_NUMBER";  // Your Twilio phone number

// Get user phone number from the request
$data = json_decode(file_get_contents("php://input"), true);
$driverPhone = $data['driverPhone'];  // User's phone number

// Create Twilio client
$client = new Client($account_sid, $auth_token);

// Send SMS
$message = $client->messages->create(
    $driverPhone,  // To phone number
    [
        'from' => $from_number,  // From your Twilio phone number
        'body' => 'Emergency ambulance request. Check tracker app to view location.'
    ]
);

// Return success message
echo json_encode(["message" => "SMS sent successfully"]);
?>
