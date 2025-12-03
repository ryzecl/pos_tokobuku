<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Groq api</title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #0f0f1a;
      color: #e0e0e0;
      height: 100vh;
      display: flex;
      flex-direction: column;
    }
    header {
      background: #1a1a2e;
      padding: 1rem;
      text-align: center;
      font-size: 1.5rem;
      font-weight: bold;
      letter-spacing: 1px;
      box-shadow: 0 2px 10px rgba(0,255,136,0.2);
    }
    #chat-container {
      flex: 1;
      overflow-y: auto;
      padding: 1rem;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }
    .message {
      max-width: 80%;
      padding: 12px 16px;
      border-radius: 18px;
      line-height: 1.5;
      word-wrap: break-word;
    }
    .user {
      align-self: flex-end;
      background: #00ff88;
      color: #000;
      border-bottom-right-radius: 4px;
    }
    .bot {
      align-self: flex-start;
      background: #16213e;
      border: 1px solid #334466;
      border-bottom-left-radius: 4px;
    }
    .typing {
      font-style: italic;
      color: #00ff88;
    }
    #input-area {
      display: flex;
      padding: 1rem;
      background: #1a1a2e;
      gap: 10px;
      border-top: 1px solid #334466;
    }
    #prompt {
      flex: 1;
      padding: 14px 16px;
      border: none;
      border-radius: 30px;
      background: #16213e;
      color: #fff;
      font-size: 1rem;
      outline: none;
    }
    #prompt:focus {
      background: #1e2a44;
    }
    button {
      padding: 0 20px;
      background: #00ff88;
      color: #000;
      border: none;
      border-radius: 30px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.2s;
    }
    button:hover { background: #00cc70; }
    button:disabled { background: #555; cursor: not-allowed; }
    footer {
      text-align: center;
      padding: 0.5rem;
      font-size: 0.8rem;
      color: #666;
    }
  </style>
</head>
<body>

  <header>GROQ API</header>

  <div id="chat-container">
    <div class="bot message">Welcome to groq</div>
  </div>

  <div id="input-area">
    <input type="text" id="prompt" placeholder="Ketik pesan di sini..." autocomplete="off" />
    <button id="send-btn">Kirim</button>
  </div>

  <a href="https://github.com/hildan-anugrah/groq-rest-api"><footer>by hildan-anugrah</footer></a>

  <script>
    const chatContainer = document.getElementById('chat-container');
    const promptInput = document.getElementById('prompt');
    const sendBtn = document.getElementById('send-btn');

    promptInput.focus();

    promptInput.addEventListener('keypress', e => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        kirimPesan();
      }
    });

    sendBtn.addEventListener('click', kirimPesan);

    async function kirimPesan() {
      const text = promptInput.value.trim();
      if (!text) return;

      tambahPesan(text, 'user');
      promptInput.value = '';
      sendBtn.disabled = true;

      const typingMsg = tambahPesan('Groq Mengetik...', 'bot');
      typingMsg.classList.add('typing');

      try {
        const res = await fetch('http://localhost:3000/generate-text', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ prompt: text })
        });

        const data = await res.json();

        chatContainer.removeChild(typingMsg);
        tambahPesan(data.result || "Error, Tidak ada jawaban...", 'bot');

      } catch (err) {
        chatContainer.removeChild(typingMsg);
        tambahPesan('Error: Server mati atau CORS bermasalah', 'bot');
        console.error(err);
      } finally {
        sendBtn.disabled = false;
        promptInput.focus();
      }
    }

    function tambahPesan(text, sender) {
      const div = document.createElement('div');
      div.className = `message ${sender}`;
      div.textContent = text;
      chatContainer.appendChild(div);
      chatContainer.scrollTop = chatContainer.scrollHeight;
      return div;
    }
  </script>
</body>
</html>