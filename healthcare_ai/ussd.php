<?php
// Database Connection
$conn = new mysqli("localhost", "root", "", "afyabora_healthcare");

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get USSD Inputs
$sessionId   = $_POST["sessionId"];
$serviceCode = $_POST["serviceCode"];
$phoneNumber = $_POST["phoneNumber"];
$text        = $_POST["text"];

$response = "";

// Explode the input text
$inputArray = explode("*", $text);
$level = count($inputArray);

// Check if input is empty (Main Menu)
if ($text == "") {
    $response  = "CON Welcome to Afyabora Healthcare \n";
    $response .= "1. Register\n";
    $response .= "2. Request Ambulance\n";
    $response .= "3. Check Appointment\n";
    $response .= "4. Find Nearest Facility\n";
    $response .= "0. Exit";
} elseif ($inputArray[0] == "1") {
    // **PATIENT REGISTRATION FLOW**
    if ($level == 1) {
        $response = "CON Enter your First Name:";
    } elseif ($level == 2) {
        $response = "CON Enter your Last Name:";
    } elseif ($level == 3) {
        $response = "CON Enter your Gender (Male/Female):";
    } elseif ($level == 4) {
        $response = "CON Enter your Email:";
    } elseif ($level == 5) {
        $response = "CON Set your Password:";
    } elseif ($level == 6) {
        $response = "CON Confirm your Password:";
    } elseif ($level == 7) {
        // Get user input values
        $fname = $inputArray[1];
        $lname = $inputArray[2];
        $gender = $inputArray[3];
        $email = $inputArray[4];
        $password = $inputArray[5];
        $cpassword = $inputArray[6];

        // Check if passwords match
        if ($password !== $cpassword) {
            $response = "END Passwords do not match! Try again.";
        } else {
            // Insert patient into database
            $stmt = $conn->prepare("INSERT INTO patreg (fname, lname, gender, email, contact, password, cpassword) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $fname, $lname, $gender, $email, $phoneNumber, $password, $cpassword);
            $stmt->execute();

            $response = "END Registration successful! Welcome, $fname.";
        }
    }
} elseif ($inputArray[0] == "2") {
    $response = "CON Enter your location for the ambulance:";
} elseif ($inputArray[0] == "3") {
    // **CHECK APPOINTMENT FLOW**
    if ($level == 1) {
        $response = "CON Enter your registered phone number:";
    } elseif ($level == 2) {
        $contact = $inputArray[1];

        // Fetch the latest appointment for the given phone number
        $stmt = $conn->prepare("SELECT doctor, docFees, appdate, apptime, userStatus, doctorStatus FROM appointmenttb WHERE contact = ? ORDER BY appdate DESC, apptime DESC LIMIT 1");
        $stmt->bind_param("s", $contact);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            $doctor = $row["doctor"];
            $fee = $row["docFees"];
            $date = $row["appdate"];
            $time = $row["apptime"];
            $userStatus = $row["userStatus"];
            $doctorStatus = $row["doctorStatus"];

            // Determine appointment status
            if ($userStatus == 1 && $doctorStatus == 1) {
                $status = "Confirmed ✅";
            } elseif ($userStatus == 0) {
                $status = "Cancelled ❌";
            } elseif ($doctorStatus == 0) {
                $status = "Pending ⏳";
            } else {
                $status = "Unknown 🤔";
            }

            // Send appointment details
            $response = "END Your Appointment Details: \n";
            $response .= "Doctor: $doctor \n";
            $response .= "Fee: $fee KES \n";
            $response .= "Date: $date \n";
            $response .= "Time: $time \n";
            $response .= "Status: $status";
        } else {
            $response = "END No appointment found for this number!";
        }
    }
}elseif ($inputArray[0] == "4") {
    $response = "CON Enter your location to find the nearest facility:";
} elseif ($inputArray[0] == "0") {
    $response = "END Thank you for using Afyabora Healthcare.";
} else {
    $response = "END Invalid option. Try again.";
}

// Output response
header('Content-type: text/plain');
echo $response;

// Close the database connection
$conn->close();
?>
