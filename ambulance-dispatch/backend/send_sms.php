<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once 'vendor/autoload.php';
use Twilio\Rest\Client;

header("Content-Type: application/json");

// Twilio credentials (use environment variables for security)
$account_sid = getenv("TWILIO_ACCOUNT_SID") ?: "YOUR_TWILIO_ACCOUNT_SID";
$auth_token = getenv("TWILIO_AUTH_TOKEN") ?: "YOUR_TWILIO_AUTH_TOKEN";
$from_number = getenv("TWILIO_PHONE_NUMBER") ?: "YOUR_TWILIO_PHONE_NUMBER"; 

// Read and decode the JSON request body
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data['userPhoneNumber']) || empty($data['userPhoneNumber'])) {
    http_response_code(400);
    echo json_encode(["error" => "User phone number is required."]);
    exit;
}

$userPhoneNumber = trim($data['userPhoneNumber']);

// Basic phone number validation (modify based on country format)
if (!preg_match('/^\+?[1-9]\d{7,14}$/', $userPhoneNumber)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid phone number format."]);
    exit;
}

try {
    // Initialize Twilio client
    $client = new Client($account_sid, $auth_token);

    // Send SMS
    $message = $client->messages->create(
        $userPhoneNumber,
        [
            'from' => $from_number,
            'body' => 'The ambulance has arrived at your location.'
        ]
    );

    echo json_encode(["message" => "SMS sent successfully", "sid" => $message->sid]);

} catch (Twilio\Exceptions\RestException $e) {
    http_response_code(500);
    error_log("Twilio Error: " . $e->getMessage()); // Log error for debugging

    $error_message = "Failed to send SMS. Please try again later.";
    
    // Return user-friendly error without exposing details
    ob_clean();
    echo json_encode(["error" => $error_message]);
    exit;

}
?>
