<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AI Health Diagnosis Assistant</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
function sendQuery() {
    let query = document.getElementById("query").value;
    if (!query.trim()) return;

    let chatBox = document.getElementById("chatbox");
    chatBox.innerHTML += `<div class="user-msg">🧑‍⚕️ You: ${query}</div>`;

    // Show AI typing indicator
    chatBox.innerHTML += `<div class="bot-msg loading">🤖 AI is analyzing...</div>`;
    chatBox.scrollTop = chatBox.scrollHeight;

    fetch("healthcare_ai/chatbot.php", { // Ensure correct API path
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ query }),
    })
    .then((res) => res.text())
    .then((response) => {
        // Remove loading message
        document.querySelector(".loading").remove();
        chatBox.innerHTML += `<div class="bot-msg">🤖 ${response}</div>`;
        chatBox.scrollTop = chatBox.scrollHeight;
        document.getElementById("query").value = "";
    })
    .catch(() => {
        document.querySelector(".loading").remove();
        chatBox.innerHTML += `<div class="bot-msg error">⚠️ Error: Unable to get diagnosis.</div>`;
    });
}

function toggleChat() {
    let chatContainer = document.getElementById("chat-container");
    chatContainer.classList.toggle("open");

    if (chatContainer.classList.contains("open")) {
        showGreeting();
    }
}

function showGreeting() {
    let chatBox = document.getElementById("chatbox");
    if (!chatBox.dataset.greeted) {
        chatBox.innerHTML += `<div class="bot-msg">🤖 Hello! Welcome to Afya Bora AI Health Diagnosis. Please provide your symptoms, separated by commas (e.g., fever, headache, cough).</div>`;
        chatBox.dataset.greeted = "true"; // Mark as greeted
        chatBox.scrollTop = chatBox.scrollHeight;
    }
}
</script>

    <style>
      /* Floating Chat Button */
      .chat-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: linear-gradient(135deg, #0abfbc, #056676);
        color: white;
        border: none;
        border-radius: 10px;
        width: 150px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        transition: transform 0.3s ease-in-out;
      }
      .chat-btn:hover {
        transform: scale(1.1);
      }

      /* Chat Container */
      .chat-container {
        position: fixed;
        bottom: 100px;
        right: 20px;
        width: 360px;
        max-width: 90%;
        background: white;
        border-radius: 15px;
        box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
        overflow: hidden;
        display: none;
        flex-direction: column;
        transition: all 0.3s ease-in-out;
        z-index: 1000;
      }
      .chat-container.open {
        display: flex;
      }

      /* Chat Header */
      .chat-header {
        background: linear-gradient(135deg, #0abfbc, #056676);
        color: white;
        padding: 15px;
        font-size: 18px;
        font-weight: bold;
        text-align: center;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      .chat-header span {
        margin-left: 10px;
      }
      .close-btn {
        position: absolute;
        right: 15px;
        top: 10px;
        font-size: 18px;
        cursor: pointer;
        color: white;
      }

      /* Chat Messages */
      .chatbox {
        max-height: 300px;
        overflow-y: auto;
        padding: 10px;
        display: flex;
        flex-direction: column;
        background: #e6f7f8;
      }
      .user-msg,
      .bot-msg {
        padding: 10px 15px;
        margin: 5px;
        border-radius: 12px;
        max-width: 80%;
        font-size: 14px;
      }
      .user-msg {
        background: #d1f2eb;
        align-self: flex-end;
        color: #056676;
        font-weight: bold;
      }
      .bot-msg {
        background: #0abfbc;
        color: white;
        align-self: flex-start;
      }
      .bot-msg.error {
        background: #ff6b6b;
      }

      /* Input Section */
      .chat-input {
        display: flex;
        border-top: 1px solid #ddd;
        background: white;
      }
      .chat-input input {
        flex: 1;
        border: none;
        padding: 12px;
        font-size: 14px;
        outline: none;
      }
      .chat-input button {
        background: #056676;
        color: white;
        border: none;
        padding: 12px;
        cursor: pointer;
        font-size: 14px;
        transition: 0.3s;
      }
      .chat-input button:hover {
        background: #034c4c;
      }

      /* Animation */
      @keyframes fadeIn {
        from {
          opacity: 0;
          transform: scale(0.95);
        }
        to {
          opacity: 1;
          transform: scale(1);
        }
      }
      .chat-container.open {
        animation: fadeIn 0.3s ease-in-out;
      }
    </style>
  </head>
  <body>
    <!-- Floating Chat Button -->
    <button class="chat-btn" onclick="toggleChat()">AI Diagnosis</button>

    <!-- Chat Container -->
    <div id="chat-container" class="chat-container">
      <div class="chat-header">
        <span>Afya Bora AI Health Diagnosis</span>
        <span class="close-btn" onclick="toggleChat()">✖</span>
      </div>
      <div id="chatbox" class="chatbox"></div>
      <div class="chat-input">
        <input
          type="text"
          id="query"
          placeholder="Describe your symptoms..."
          onkeypress="if(event.key === 'Enter') sendQuery()"
        />
        <button onclick="sendQuery()">Get AI Diagnosis</button>
      </div>
    </div>
  </body>
</html>
