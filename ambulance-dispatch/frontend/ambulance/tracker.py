import time
import requests
import geocoder  # To get GPS coordinates

# Configuration
API_URL = "http://localhost/ambulance-dispatch/backend/firebase_update.php"
AMBULANCE_ID = "AMB123"  # Replace with actual ambulance ID

def get_location():
    """Fetch the current GPS coordinates."""
    g = geocoder.ip('me')  # Get approximate location using IP (use geocoder.osm() for GPS devices)
    if g.ok:
        return g.latlng  # Returns (latitude, longitude)
    return None

def update_location():
    """Send location updates to the server every 5 seconds."""
    while True:
        location = get_location()
        if location:
            data = {
                "ambulance_id": AMBULANCE_ID,
                "latitude": location[0],
                "longitude": location[1],
                "status": "on_way"
            }
            try:
                response = requests.post(API_URL, json=data, headers={"Content-Type": "application/json"})
                print("Update Response:", response.json())
            except requests.RequestException as e:
                print("Error updating location:", e)
        else:
            print("Could not retrieve location")

        time.sleep(5)  # Wait 5 seconds before sending the next update

if __name__ == "__main__":
    update_location()
