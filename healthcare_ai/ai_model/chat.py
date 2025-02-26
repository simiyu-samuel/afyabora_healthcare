from flask import Flask, request, jsonify
from transformers import pipeline

app = Flask(__name__)

# Load AI model
chatbot = pipeline("text-generation", model="facebook/blenderbot-400M-distill")

@app.route('/chat', methods=['POST'])
def chat():
    data = request.json
    user_query = data['query']
    
    response = chatbot(user_query, max_length=100)[0]['generated_text']
    return jsonify({'response': response})

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5001)
