<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>DaeBook Assistant - Dea</title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Segoe UI',sans-serif; background:#0f0f1a; color:#e0e0e0; height:100vh; display:flex; flex-direction:column; }
    header { background:#1a1a2e; padding:1rem; text-align:center; font-size:1.5rem; font-weight:bold; box-shadow:0 2px 15px rgba(0,255,136,0.2); }
    #chat-container { flex:1; overflow-y:auto; padding:1rem; display:flex; flex-direction:column; gap:1rem; padding-bottom:80px; }
    .message { max-width:80%; padding:12px 18px; border-radius:18px; line-height:1.5; word-wrap:break-word; }
    .user { align-self:flex-end; background:#00ff88; color:#000; border-bottom-right-radius:4px; }
    .bot { align-self:flex-start; background:#16213e; border:1px solid #334466; border-bottom-left-radius:4px; }
    .typing { font-style:italic; color:#00ff88; opacity:0.9; }
    #input-area { position:fixed; bottom:0; left:0; right:0; display:flex; padding:1rem; background:#1a1a2e; gap:12px; border-top:1px solid #334466; }
    #prompt { flex:1; padding:14px 18px; border:none; border-radius:30px; background:#16213e; color:#fff; font-size:1rem; outline:none; }
    #prompt:focus { background:#1e2a44; }
    button { padding:0 28px; background:#00ff88; color:#000; border:none; border-radius:30px; font-weight:bold; cursor:pointer; }
    button:hover { background:#00cc70; }
    button:disabled { background:#006644; cursor:not-allowed; opacity:0.7; }
    footer { text-align:center; padding:0.5rem; font-size:0.8rem; color:#666; margin-top:10px; }
  </style>
</head>
<body>

  <header>DaeBook Assistant</header>

  <div id="chat-container">
    <div class="bot message" id="welcome-message">Sedang memuat sambutan Dea...</div>
  </div>

  <div id="input-area">
    <input type="text" id="prompt" placeholder="Tanya stok, harga, atau cari buku di sini..." autocomplete="off" autofocus />
    <button id="send-btn">Kirim</button>
  </div>

  <footer>DaeBook × Groq Assistant • Real-time & Super Cepat</footer>

  <script>
    // ==================== KONFIGURASI ====================
    const API_URL = 'http://localhost:3000'; // Ganti jadi IP LAN kalau dipakai di jaringan, misal: http://192.168.1.100:3000

    // ==================== FUNGSI KEAMANAN ====================
    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    // ==================== WELCOME MESSAGE (SUDAH AMAN) ====================
    const welcomeMessages = [
      "Halo kak! Saya Dea, asisten DaeBook 📚\n\nMau cari buku, cek stok, atau tanya harga?\nLangsung ketik aja ya!",
      "Selamat datang di DaeBook! 🌟\n\nAda yang bisa Dea bantu hari ini? Cari buku, cek harga, atau tanya stok?",
      "Haii! Dea siap bantu kakak nyari buku impian 📖\n\nKetik judul, kode, atau langsung tanya stok ya!",
      "Halo! Selamat berbelanja di DaeBook 🛒\n\nDea di sini siap bantu cek stok dan harga buku favoritmu!"
    ];

    const welcomeElement = document.getElementById('welcome-message');
    const randomMsg = welcomeMessages[Math.floor(Math.random() * welcomeMessages.length)];
    welcomeElement.innerHTML = escapeHtml(randomMsg).replace(/\n/g, '<br>');

    // ==================== CHAT FUNCTION ====================
    const chatContainer = document.getElementById('chat-container');
    const promptInput = document.getElementById('prompt');
    const sendBtn = document.getElementById('send-btn');
    let isSending = false; // anti-spam

    function scrollToBottom() {
      chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    function addMessage(text, type) {
      const div = document.createElement('div');
      div.className = type + ' message';
      div.innerHTML = escapeHtml(text).replace(/\n/g, '<br>');
      chatContainer.appendChild(div);
      scrollToBottom();
    }

    async function sendMessage() {
      if (isSending) return;
      const message = promptInput.value.trim();
      if (!message) return;

      addMessage(message, 'user');
      promptInput.value = '';
      isSending = true;
      sendBtn.disabled = true;

      // Typing indicator
      const typing = document.createElement('div');
      typing.className = 'bot message typing';
      typing.textContent = 'Dea sedang mengetik...';
      typing.id = 'typing-indicator';
      chatContainer.appendChild(typing);
      scrollToBottom();

      try {
        const res = await fetch(`${API_URL}/generate-text`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ prompt: message })
        });

        const data = await res.json();
        document.getElementById('typing-indicator')?.remove();

        if (data.result) {
          addMessage(data.result, 'bot');
        } else {
          addMessage('Maaf, terjadi kesalahan: ' + (data.error || 'Tidak ada respon'), 'bot');
        }
      } catch (err) {
        document.getElementById('typing-indicator')?.remove();
        addMessage('Gak bisa nyambung ke server. Pastikan server Node.js di port 3000 lagi jalan ya!', 'bot');
        console.error(err);
      }

      isSending = false;
      sendBtn.disabled = false;
      promptInput.focus();
    }

    // ==================== EVENT LISTENER ====================
    sendBtn.addEventListener('click', sendMessage);
    promptInput.addEventListener('keydown', e => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
      }
    });

    scrollToBottom();
  </script>
</body>
</html>