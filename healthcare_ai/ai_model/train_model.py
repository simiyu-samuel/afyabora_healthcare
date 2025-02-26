import pandas as pd
import numpy as np
import pickle
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.preprocessing import LabelEncoder

# Load dataset
data = pd.read_csv(r"C:\xampp\htdocs\afyabora_healthcare\healthcare_ai\ai_model\symptoms_dataset.csv")

# Extract feature columns (all symptoms) dynamically
symptom_columns = [col for col in data.columns if col != "Disease"]

# Encode disease labels
label_encoder = LabelEncoder()
data["Disease"] = label_encoder.fit_transform(data["Disease"])

# Save Label Encoder
with open(r"C:\xampp\htdocs\afyabora_healthcare\healthcare_ai\ai_model\label_encoder.pkl", "wb") as le_file:
    pickle.dump(label_encoder, le_file)

print("✅ Label encoder saved successfully!")

# Preprocess Data
X = data[symptom_columns]  # Use dynamically extracted symptoms
y = data["Disease"]

# Split Dataset
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Train Model
model = RandomForestClassifier(n_estimators=100)
model.fit(X_train, y_train)

# Save Model & Symptom List
with open("symptom_model.pkl", "wb") as model_file:
    pickle.dump(model, model_file)

with open("symptom_list.pkl", "wb") as symp_file:
    pickle.dump(symptom_columns, symp_file)

print("✅ Model & symptom list saved successfully!")
