from flask import Flask, request, jsonify
import pickle
import numpy as np
import requests
import difflib
from flask_caching import Cache

app = Flask(__name__)

# Configure caching to improve efficiency
app.config['CACHE_TYPE'] = 'simple'
cache = Cache(app)

# Load trained model, symptom list, and label encoder
with open("symptom_model.pkl", "rb") as model_file:
    model = pickle.load(model_file)

with open("symptom_list.pkl", "rb") as symptom_file:
    symptom_list = pickle.load(symptom_file)

with open("label_encoder.pkl", "rb") as encoder_file:
    label_encoder = pickle.load(encoder_file)

# Convert symptom list to lowercase for consistent validation
symptom_mappings = {sym.lower(): sym for sym in symptom_list}

def validate_symptoms(user_input):
    """Validates a symptom using fuzzy matching if no exact match is found."""
    formatted_symptom = user_input.lower().strip().replace(" ", "_")

    if formatted_symptom in symptom_mappings:
        return symptom_mappings[formatted_symptom]
    
    matches = difflib.get_close_matches(formatted_symptom, symptom_mappings.keys(), n=1, cutoff=0.6)
    
    return symptom_mappings[matches[0]] if matches else None

def get_prescription(disease_name):
    """Fetches medication details from the RxNorm API."""
    try:
        base_url = "https://rxnav.nlm.nih.gov/REST"
        response = requests.get(f"{base_url}/approximateTerm.json?term={disease_name}&maxEntries=3")
        data = response.json()

        if "approximateGroup" not in data or "candidate" not in data["approximateGroup"]:
            return {"error": "No matching drug found for this disease."}

        rxcui_candidates = data["approximateGroup"]["candidate"]
        rxcui = rxcui_candidates[0]["rxcui"]

        drug_response = requests.get(f"{base_url}/rxcui/{rxcui}/related.json?tty=IN")
        drug_data = drug_response.json()

        if "relatedGroup" not in drug_data or "conceptGroup" not in drug_data["relatedGroup"]:
            return {"error": "No related medications found."}

        medications = [
            med["name"] for group in drug_data["relatedGroup"]["conceptGroup"] 
            if "conceptProperties" in group 
            for med in group["conceptProperties"]
        ]

        return {
            "primary_drug": medications[0] if medications else "No medication found",
            "alternative_drugs": medications[1:] if len(medications) > 1 else []
        }

    except requests.exceptions.RequestException as e:
        return {"error": f"API request failed: {str(e)}"}

@app.route('/predict', methods=['POST'])
def predict():
    try:
        data = request.json
        user_symptoms = data.get('symptoms', [])

        if not user_symptoms:
            return jsonify({"message": "I didn’t catch that. Can you please describe your symptoms again?"}), 400

        validated_symptoms = [validate_symptoms(sym) for sym in user_symptoms]
        validated_symptoms = [sym for sym in validated_symptoms if sym]

        if not validated_symptoms:
            return jsonify({"message": "Hmm... I couldn’t recognize those symptoms. Can you try rewording them?"}), 400

        # Convert symptoms into feature vector
        symptom_vector = np.array([2 if sym in validated_symptoms else 0 for sym in symptom_list]).reshape(1, -1)
        
        # Check cache first
        cache_key = "-".join(sorted(validated_symptoms))
        disease_name = cache.get(cache_key)

        if not disease_name:
            prediction_index = model.predict(symptom_vector)[0]
            disease_name = label_encoder.inverse_transform([prediction_index])[0]
            cache.set(cache_key, disease_name, timeout=300)

        prescription = get_prescription(disease_name)

        # More conversational response
        message = f"I see, based on your symptoms, it’s possible you have **{disease_name}**. "
        
        if prescription and "error" not in prescription:
            message += f"One common medication is **{prescription['primary_drug']}**."
            if prescription['alternative_drugs']:
                message += f" Other alternatives include: {', '.join(prescription['alternative_drugs'])}."
        else:
            message += "I couldn’t find specific medications, but consulting a doctor is always a good idea."

        return jsonify({"message": message})

    except Exception as e:
        return jsonify({"message": f"Oops! Something went wrong. {str(e)}"}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)
