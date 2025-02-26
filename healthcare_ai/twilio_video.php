<?php
require 'vendor/autoload.php';
use Twilio\Rest\Client;

$twilio_sid = "your_twilio_sid";
$twilio_token = "your_twilio_token";
$twilio = new Client($twilio_sid, $twilio_token);

$videoRoom = $twilio->video->v1->rooms->create([
    'uniqueName' => "HealthConsultRoom_" . rand(1000, 9999)
]);

echo "Join Video Call: https://video.twilio.com/room/" . $videoRoom->uniqueName;
?>
