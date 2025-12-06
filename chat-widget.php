<?php
/**
 * DaeBook Chat Widget - Floating Component
 * Include file ini di halaman manapun untuk menampilkan floating chat widget
 * 
 * Cara pakai:
 * <?php include 'chat-widget.php'; ?>
 */
?>

<!-- DaeBook Chat Widget -->
<div id="daebook-chat-widget">
  <!-- Chat Window (Hidden by default) -->
  <div id="daebook-chat-window" class="daebook-chat-window">
    <!-- Header -->
    <div class="daebook-chat-header">
      <div class="daebook-chat-title">
        <span class="daebook-chat-icon">💬</span>
        <span>DaeBook Assistant</span>
      </div>
      <button id="daebook-close-btn" class="daebook-close-btn">×</button>
    </div>
    
    <!-- Messages Container -->
    <div id="daebook-chat-messages" class="daebook-chat-messages">
      <div class="daebook-message daebook-bot" id="daebook-welcome-msg">
        Sedang memuat sambutan Dea...
      </div>
    </div>
    
    <!-- Input Area -->
    <div class="daebook-chat-input-area">
      <input 
        type="text" 
        id="daebook-chat-input" 
        class="daebook-chat-input" 
        placeholder="Tanya stok, harga, atau cari buku..."
        autocomplete="off"
      />
      <button id="daebook-send-btn" class="daebook-send-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
        </svg>
      </button>
    </div>
  </div>
  
  <!-- Launcher Button -->
  <button id="daebook-launcher-btn" class="daebook-launcher-btn">
    <svg class="daebook-chat-icon-svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
    </svg>
    <span class="daebook-close-icon-svg" style="display:none; font-size:28px; line-height:1;">×</span>
  </button>
</div>

<style>
/* ==================== DaeBook Chat Widget Styles ==================== */
#daebook-chat-widget * {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

/* Launcher Button */
.daebook-launcher-btn {
  position: fixed;
  bottom: 20px;
  right: 20px;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #00ff88 0%, #00cc70 100%);
  border: none;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 255, 136, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9998;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.daebook-launcher-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 16px rgba(0, 255, 136, 0.5);
}

.daebook-launcher-btn svg {
  color: #000;
}

/* Chat Window */
.daebook-chat-window {
  position: fixed;
  bottom: 90px;
  right: 20px;
  width: 380px;
  height: 600px;
  max-height: calc(100vh - 120px);
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
  display: none;
  flex-direction: column;
  z-index: 9999;
  overflow: hidden;
  animation: daebookSlideUp 0.3s ease-out;
}

.daebook-chat-window.daebook-open {
  display: flex;
}

@keyframes daebookSlideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Header */
.daebook-chat-header {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
  color: #fff;
  padding: 16px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-radius: 12px 12px 0 0;
}

.daebook-chat-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  font-size: 16px;
  font-family: 'Segoe UI', sans-serif;
}

.daebook-chat-icon {
  font-size: 20px;
}

.daebook-close-btn {
  background: transparent;
  border: none;
  color: #fff;
  font-size: 32px;
  line-height: 1;
  cursor: pointer;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  transition: background 0.2s;
}

.daebook-close-btn:hover {
  background: rgba(255, 255, 255, 0.1);
}

/* Messages Container */
.daebook-chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  background: #f5f7fa;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.daebook-chat-messages::-webkit-scrollbar {
  width: 6px;
}

.daebook-chat-messages::-webkit-scrollbar-track {
  background: #e0e0e0;
}

.daebook-chat-messages::-webkit-scrollbar-thumb {
  background: #00cc70;
  border-radius: 3px;
}

.daebook-chat-messages::-webkit-scrollbar-thumb:hover {
  background: #00a85d;
}

/* Messages */
.daebook-message {
  max-width: 85%;
  padding: 10px 14px;
  border-radius: 12px;
  line-height: 1.5;
  word-wrap: break-word;
  font-size: 14px;
  font-family: 'Segoe UI', sans-serif;
  animation: daebookFadeIn 0.3s ease-out;
}

@keyframes daebookFadeIn {
  from {
    opacity: 0;
    transform: translateY(5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.daebook-message.daebook-user {
  align-self: flex-end;
  background: linear-gradient(135deg, #00ff88 0%, #00cc70 100%);
  color: #000;
  border-bottom-right-radius: 4px;
}

.daebook-message.daebook-bot {
  align-self: flex-start;
  background: #fff;
  color: #333;
  border: 1px solid #e0e0e0;
  border-bottom-left-radius: 4px;
}

.daebook-message.daebook-typing {
  font-style: italic;
  color: #00cc70;
  opacity: 0.9;
  background: transparent;
  border: none;
}

/* Input Area */
.daebook-chat-input-area {
  display: flex;
  padding: 16px;
  background: #fff;
  border-top: 1px solid #e0e0e0;
  gap: 8px;
}

.daebook-chat-input {
  flex: 1;
  border: 2px solid #e0e0e0;
  border-radius: 24px;
  padding: 10px 16px;
  font-size: 14px;
  outline: none;
  transition: border-color 0.2s;
  font-family: 'Segoe UI', sans-serif;
}

.daebook-chat-input:focus {
  border-color: #00cc70;
}

.daebook-send-btn {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: linear-gradient(135deg, #00ff88 0%, #00cc70 100%);
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s;
  flex-shrink: 0;
}

.daebook-send-btn:hover {
  transform: scale(1.05);
}

.daebook-send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.daebook-send-btn svg {
  color: #000;
}

/* Mobile Responsive */
@media (max-width: 480px) {
  .daebook-chat-window {
    width: calc(100vw - 20px);
    height: calc(100vh - 100px);
    bottom: 10px;
    right: 10px;
    max-height: calc(100vh - 100px);
  }
  
  .daebook-launcher-btn {
    bottom: 16px;
    right: 16px;
  }
}
</style>

<script>
(function() {
  // ==================== KONFIGURASI ====================
  const API_URL = 'chat-popup.php';
  
  // ==================== ELEMEN DOM ====================
  const launcherBtn = document.getElementById('daebook-launcher-btn');
  const chatWindow = document.getElementById('daebook-chat-window');
  const closeBtn = document.getElementById('daebook-close-btn');
  const messagesContainer = document.getElementById('daebook-chat-messages');
  const inputField = document.getElementById('daebook-chat-input');
  const sendBtn = document.getElementById('daebook-send-btn');
  const welcomeMsg = document.getElementById('daebook-welcome-msg');
  const chatIconSvg = launcherBtn.querySelector('.daebook-chat-icon-svg');
  const closeIconSvg = launcherBtn.querySelector('.daebook-close-icon-svg');
  
  let isSending = false;
  let isOpen = false;
  
  // ==================== FUNGSI KEAMANAN ====================
  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
  
  // ==================== WELCOME MESSAGE ====================
  const welcomeMessages = [
    "Halo kak! Saya Dea, asisten DaeBook 📚\n\nMau cari buku, cek stok, atau tanya harga?\nLangsung ketik aja ya!",
    "Selamat datang di DaeBook! 🌟\n\nAda yang bisa Dea bantu hari ini? Cari buku, cek harga, atau tanya stok?",
    "Haii! Dea siap bantu kakak nyari buku impian 📖\n\nKetik judul, kode, atau langsung tanya stok ya!",
    "Halo! Selamat berbelanja di DaeBook 🛒\n\nDea di sini siap bantu cek stok dan harga buku favoritmu!"
  ];
  
  const randomWelcome = welcomeMessages[Math.floor(Math.random() * welcomeMessages.length)];
  welcomeMsg.innerHTML = escapeHtml(randomWelcome).replace(/\n/g, '<br>');
  
  // ==================== FUNGSI CHAT ====================
  function toggleChat() {
    isOpen = !isOpen;
    
    if (isOpen) {
      chatWindow.classList.add('daebook-open');
      chatIconSvg.style.display = 'none';
      closeIconSvg.style.display = 'block';
      setTimeout(() => inputField.focus(), 300);
    } else {
      chatWindow.classList.remove('daebook-open');
      chatIconSvg.style.display = 'block';
      closeIconSvg.style.display = 'none';
    }
  }
  
  function scrollToBottom() {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  }
  
  function addMessage(text, type) {
    const msgDiv = document.createElement('div');
    msgDiv.className = `daebook-message daebook-${type}`;
    msgDiv.innerHTML = escapeHtml(text).replace(/\n/g, '<br>');
    messagesContainer.appendChild(msgDiv);
    scrollToBottom();
  }
  
  async function sendMessage() {
    if (isSending) return;
    
    const message = inputField.value.trim();
    if (!message) return;
    
    addMessage(message, 'user');
    inputField.value = '';
    isSending = true;
    sendBtn.disabled = true;
    
    // Typing indicator
    const typingDiv = document.createElement('div');
    typingDiv.className = 'daebook-message daebook-typing';
    typingDiv.id = 'daebook-typing-indicator';
    typingDiv.textContent = 'Dea sedang mengetik...';
    messagesContainer.appendChild(typingDiv);
    scrollToBottom();
    
    try {
      const res = await fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'prompt=' + encodeURIComponent(message)
      });
      
      const data = await res.json();
      document.getElementById('daebook-typing-indicator')?.remove();
      
      if (data.success && data.result) {
        addMessage(data.result, 'bot');
      } else {
        addMessage('Maaf, terjadi kesalahan: ' + (data.error || 'Tidak ada respon'), 'bot');
      }
    } catch (err) {
      document.getElementById('daebook-typing-indicator')?.remove();
      addMessage('Gak bisa nyambung ke server. Pastikan server Node.js di port 3000 lagi jalan ya!', 'bot');
      console.error(err);
    }
    
    isSending = false;
    sendBtn.disabled = false;
    inputField.focus();
  }
  
  // ==================== EVENT LISTENERS ====================
  launcherBtn.addEventListener('click', toggleChat);
  closeBtn.addEventListener('click', toggleChat);
  sendBtn.addEventListener('click', sendMessage);
  
  inputField.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });
  
})();
</script>
