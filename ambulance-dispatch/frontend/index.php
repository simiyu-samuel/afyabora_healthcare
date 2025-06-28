<?php
session_start();

$_SESSION['pid'] = "40";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambulance Dispatch</title>
    
    <!-- Leaflet.js for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-database-compat.js"></script>
    
    <!-- Custom Styles -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        
        h1 {
            color: #00796b;
            margin-top: 20px;
        }
        
        button {
            background-color: #00796b;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }
        
        button:hover {
            background-color: #004d40;
        }
        
        #map {
            height: 400px;
            width: 90%;
            margin: 20px auto;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .input-container {
            margin: 20px auto;
            width: 90%;
            max-width: 400px;
        }
        
        input {
            width: 100%;
            padding: 10px;
            border: 2px solid #00796b;
            border-radius: 5px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <h1>Emergency Ambulance Request</h1>
    <div class="input-container">
        <input type="text" id="phoneNumber" placeholder="Enter your phone number" required/>
    </div>
    <button onclick="requestAmbulance()">Request Ambulance</button>
    <div id="map"></div>
    <script>
        // Firebase configuration
        const firebaseConfig = {
            //fill your details 
        };

        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);
        const db = firebase.database();

        let map, userMarker, ambulanceMarker;

        // Initialize the map
        function initMap(userLat, userLon) {
            if (!map) {
                map = L.map('map').setView([userLat, userLon], 14);

                // OpenStreetMap tile layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);
            }

            // Add user location marker
            if (userMarker) {
                userMarker.setLatLng([userLat, userLon]);
            } else {
                userMarker = L.marker([userLat, userLon]).addTo(map)
                    .bindPopup("Your Location")
                    .openPopup();
            }
        }
function requestAmbulance() {
    navigator.geolocation.getCurrentPosition((position) => {
        const lat = position.coords.latitude;
        const lon = position.coords.longitude;
        const userPhoneNumber = document.getElementById('phoneNumber').value;

        console.log("Sending Data:", { latitude: lat, longitude: lon, phone: userPhoneNumber });

        fetch("../backend/request_ambulance.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ latitude: lat, longitude: lon, phone: userPhoneNumber })
        })
        .then(response => response.json())
        .then(data => {
            console.log("Response Data:", data);
            if (data.ambulance_id) {
                notify(data.driverPhone);
                trackAmbulance(data.ambulance_id, lat, lon, userPhoneNumber);

            } else {
                alert("No ambulance available.");
            }
        })
        .catch(error => console.error("Error requesting ambulance:", error));
    }, (error) => {
        alert("Error getting location: " + error.message);
    });
}


let routeLine; // Global variable to store the route line

function trackAmbulance(ambulanceId, userLat, userLon, userPhoneNumber) {
    db.ref("ambulances/" + ambulanceId).on("value", (snapshot) => {
        if (snapshot.exists()) {
            const { latitude, longitude, status } = snapshot.val();

            if (status !== 'arrived') {
                const distance = calculateDistance(userLat, userLon, latitude, longitude);

                if (distance <= 0.1) {  
                    updateAmbulanceStatus(ambulanceId, 'arrived');
                    sendArrivalSMS(userPhoneNumber);
                }
            }

            // Update ambulance marker on the map
            if (ambulanceMarker) {
                ambulanceMarker.setLatLng([latitude, longitude]);
            } else {
                ambulanceMarker = L.marker([latitude, longitude]).addTo(map)
                    .bindPopup("Ambulance Location")
                    .openPopup();
            }

            // Draw or update the route line
            if (routeLine) {
                routeLine.setLatLngs([[userLat, userLon], [latitude, longitude]]);
            } else {
                routeLine = L.polyline([[userLat, userLon], [latitude, longitude]], {
                    color: 'red', // Line color
                    weight: 4, // Thickness
                    dashArray: '5, 10' // Dashed line style
                }).addTo(map);
            }
        }
    });
}


        function updateAmbulanceStatus(ambulanceId, status) {
            db.ref("ambulances/" + ambulanceId).update({ status: status })
            .then(() => console.log("Ambulance status updated to", status))
            .catch(error => console.error("Error updating ambulance status:", error));
        }

        function sendArrivalSMS(userPhoneNumber) {
            fetch("backend/send_sms.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ userPhoneNumber: userPhoneNumber })
            })
            .then(response => response.json())
            .then(data => {
                console.log(data.message);
            })
            .catch(error => console.error("Error sending SMS:", error));
        }

        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) ** 2;
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        function notify(driverPhone){
            fetch("../backend/notify.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ driverPhoneNumber: driverPhone })
            })
            .then(response => response.json())
            .then(data => {
                console.log(data.message);
            })
            .catch(error => console.error("Error sending SMS:", error));
        }
        window.onload = function() {
            navigator.geolocation.getCurrentPosition(position => initMap(position.coords.latitude, position.coords.longitude));
        };
    </script>
</body>
</html>
