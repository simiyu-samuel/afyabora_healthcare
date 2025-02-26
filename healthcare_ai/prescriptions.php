<?php
require 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $doctor_id = $_POST['doctor_id'];
    $medication = $_POST['medication'];
    $dosage = $_POST['dosage'];
    $instructions = $_POST['instructions'];

    DB::query("INSERT INTO prescriptions (user_id, doctor_id, medication, dosage, instructions) VALUES (?, ?, ?, ?, ?)", 
              [$user_id, $doctor_id, $medication, $dosage, $instructions]);

    echo "Prescription saved!";
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $user_id = $_GET['user_id'];
    $prescriptions = DB::query("SELECT * FROM prescriptions WHERE user_id = ?", [$user_id]);
    echo json_encode($prescriptions);
}
?>
