<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $query = $_POST['query'];

    if (empty($query)) {
        echo "🤖 AI: Hmm... I didn't catch that. Could you describe your symptoms again? 😊";
        exit;
    }

    $data = json_encode(["symptoms" => explode(", ", $query)]);
    $ch = curl_init("http://127.0.0.1:5000/predict");

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['message'])) {
        echo "🤖 AI: " . $result['message'];
    } else {
        echo "⚠️ Oops! I had trouble processing that. Can you try again?";
    }
}
