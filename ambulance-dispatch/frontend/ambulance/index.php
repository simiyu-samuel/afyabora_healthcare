<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambulance Tracking</title>
    
    <!-- Leaflet.js for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-database-compat.js"></script>
    
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff;
            text-align: center;
            margin: 0;
            padding: 0;
        }
        h1 {
            color: #00796b;
            margin-top: 20px;
        }
        #map {
            height: 500px;
            width: 90%;
            margin: 20px auto;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <h1>Ambulance Tracking</h1>
    <div id="map"></div>
    <script>
      // Firebase configuration
// Firebase configuration
const firebaseConfig = {
    apiKey: "AIzaSyCuf73zMW8wIoG_Wu8FT38KHQtdOvLRS-0",
    authDomain: "healthcare-b88c6.firebaseapp.com",
    databaseURL: "https://healthcare-b88c6-default-rtdb.firebaseio.com",
    projectId: "healthcare-b88c6",
    storageBucket: "healthcare-b88c6.appspot.com",
    messagingSenderId: "205464558655",
    appId: "1:205464558655:web:ca43fe49d832c2594a2c8a",
    measurementId: "G-BFNPFHMFHV"
};

// Initialize Firebase
firebase.initializeApp(firebaseConfig);
const db = firebase.database();

let map, ambulanceMarker, patientMarker, polyline;

function initMap(ambulanceLat, ambulanceLon) {
    map = L.map('map').setView([ambulanceLat, ambulanceLon], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
}

function trackAmbulance(ambulanceId) {
    const ambulanceRef = db.ref("ambulances/" + ambulanceId);

    // Track ambulance in real-time
    ambulanceRef.on("value", (snapshot) => {
        if (snapshot.exists()) {
            const data = snapshot.val();
            const { latitude, longitude, patientLat, patientLog } = data;

            if (!latitude || !longitude || !patientLat || !patientLog) {
                console.log("Missing coordinates in ambulance data:", data);
                return;
            }

            // Initialize map if not already initialized
            if (!map) {
                initMap(latitude, longitude);
            }

            // Update ambulance marker
            if (ambulanceMarker) {
                ambulanceMarker.setLatLng([latitude, longitude])
                    .bindPopup("Ambulance Location").openPopup();
            } else {
                ambulanceMarker = L.marker([latitude, longitude], {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',  
                    iconSize: [30, 50]
                })
            }).addTo(map).bindPopup("Ambulance Location").openPopup();

            }

            // Update patient marker
            if (patientMarker) {
                patientMarker.setLatLng([patientLat, patientLog]);
            } else {
                patientMarker = L.marker([patientLat, patientLog], {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png',
                        iconSize: [20, 20]
                    })
                }).addTo(map).bindPopup("Patient Location").openPopup();
            }

            // Update route line
            if (polyline) {
                polyline.setLatLngs([[latitude, longitude], [patientLat, patientLog]]);
            } else {
                polyline = L.polyline([[latitude, longitude], [patientLat, patientLog]], {
                    color: 'red',
                    weight: 3
                }).addTo(map);
            }

            // Adjust map view dynamically
            map.setView([latitude, longitude], 14);
        } else {
            console.log("No ambulance data found.");
        }
    }, (error) => {
        console.error("Error retrieving ambulance data:", error);
    });
}

window.onload = function () {
    navigator.geolocation.getCurrentPosition(position => initMap(position.coords.latitude, position.coords.longitude));
    const ambulanceId = 23; // Replace with dynamic ambulance ID
    trackAmbulance(ambulanceId);
};

</script>
</body>
</html>
