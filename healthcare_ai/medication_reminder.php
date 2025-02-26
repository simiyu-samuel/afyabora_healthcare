<?php
require 'vendor/autoload.php';
use Twilio\Rest\Client;

function sendReminder($phone, $message) {
    $twilio_sid = "your_twilio_sid";
    $twilio_token = "your_twilio_token";
    $twilio = new Client($twilio_sid, $twilio_token);

    $twilio->messages->create($phone, [
        'from' => "your_twilio_number",
        'body' => $message
    ]);
}

// Get all prescriptions for today
$prescriptions = DB::query("SELECT * FROM prescriptions WHERE DATE(created_at) = CURDATE()");
foreach ($prescriptions as $prescription) {
    $user = DB::query("SELECT phone FROM users WHERE id = ?", [$prescription['user_id']])[0];
    sendReminder($user['phone'], "Reminder: Take your medicine - " . $prescription['medication']);
}
?>
