<?php
/**
 * DaeBook Chat Widget - Integrated Frontend & Backend
 * File ini menggabungkan UI widget dan API handler dalam satu file
 */

// ==================== BACKEND API HANDLER (DIPERBAIKI) ====================
// Blok ini dijalankan pertama kali. Jika ini adalah request API,
// proses di sini dan hentikan eksekusi script.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'chat') {
    // Matikan error display untuk mencegah output HTML di response JSON
    ini_set('display_errors', 0);
    
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    
    // Ambil input dari request
    $rawInput = file_get_contents('php://input');
    $prompt = null;
    
    if (!empty($rawInput)) {
        $jsonData = json_decode($rawInput, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($jsonData['prompt'])) {
            $prompt = $jsonData['prompt'];
        }
    }
    
    if ($prompt === null && isset($_POST['prompt'])) {
        $prompt = $_POST['prompt'];
    }
    
    if (empty($prompt)) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Prompt tidak boleh kosong!'
        ]);
        exit;
    }
    
    try {
        // Siapkan data untuk dikirim ke server Node.js
        $postData = json_encode(['prompt' => $prompt]);
        
        // Inisialisasi cURL
        $ch = curl_init('http://localhost:3000/generate-text');
        
        // Set opsi cURL
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postData),
                'User-Agent: DaeBook-Chat/1.0'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        // Eksekusi request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($response === false || !empty($curlError)) {
            throw new Exception('Gagal terhubung ke server AI: ' . $curlError);
        }
        
        if ($httpCode !== 200) {
            throw new Exception('Server AI mengembalikan error (HTTP ' . $httpCode . ')');
        }
        
        $responseData = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Response dari server tidak valid');
        }
        
        // Kembalikan response dari server Node.js TANPA MODIFIKASI
        echo $response;
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Maaf, saat ini sedang ada gangguan. Silakan coba lagi beberapa saat lagi. 🙏'
        ]);
        
        error_log('DaeBook Chat Error: ' . $e->getMessage());
    }
    
    exit; // PENTING: Hentikan eksekusi setelah menangani API
}

// ==================== KEAMANAN: CEK AKSES LANGSUNG ====================
// Blok ini hanya dijalankan jika BUKAN request API.
// Ini mencegah akses langsung ke bagian UI dari file ini.
if (!defined('ALLOW_CHAT_ACCESS')) {
    // Jika diakses langsung, redirect ke homepage
    header('Location: /');
    exit();
}

// Jika bukan POST request atau bukan action chat, lanjutkan ke rendering UI
?>
<div id="daebook-chat-widget">
    <div id="daebook-chat-window" class="daebook-chat-window">
        <div class="daebook-chat-header">
            <div class="daebook-chat-title">
                <span class="daebook-chat-icon">🤖</span>
                <span>DaeBook Assistant</span>
            </div>
            <button id="daebook-close-btn" class="daebook-close-btn" aria-label="Tutup Obrolan">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6l-12 12M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div id="daebook-chat-messages" class="daebook-chat-messages">
            <div class="daebook-message daebook-bot daebook-welcome" id="daebook-welcome-msg">
                Sedang memuat sambutan Dea...
            </div>
        </div>
        
        <div class="daebook-chat-input-area">
            <input 
                type="text" 
                id="daebook-chat-input" 
                class="daebook-chat-input" 
                placeholder="Tanya stok, harga, atau cari buku..."
                autocomplete="off"
            />
            <button id="daebook-send-btn" class="daebook-send-btn" aria-label="Kirim Pesan">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
                </svg>
            </button>
        </div>
    </div>
    
    <button id="daebook-launcher-btn" class="daebook-launcher-btn" aria-label="Buka Obrolan">
        <svg class="daebook-chat-icon-svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        <svg class="daebook-close-icon-svg" style="display:none;" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 6L6 18M6 6l12 12"/>
        </svg>
    </button>
</div>

<style>
/* Import Poppins font */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

/* ==================== DaeBook Chat Widget Styles (UPDATED) ==================== */
:root {
    /* Color Palette from Daebook Website */
    --db-primary-light: #A78BFA; /* Lavender */
    --db-primary-dark: #8B5CF6; /* Violet */
    --db-accent: #EC4899; /* Pink */
    --db-bg-dark: #0B0B15; /* Dark Background */
    --db-bg-medium: #13132B; /* Medium Background */
    --db-bg-light: #1A1A35; /* Light Background */
    --db-text-light: #F9FAFB;
    --db-border: rgba(255, 255, 255, 0.1);
    --db-shadow: rgba(0, 0, 0, 0.4);
}

#daebook-chat-widget * {
    box-sizing: border-box;
    margin: 0;
    font-family: 'Poppins', sans-serif;
}

/* Launcher Button */
.daebook-launcher-btn {
    position: fixed;
    bottom: 24px;
    right: 24px;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--db-primary-dark) 0%, var(--db-accent) 100%);
    border: none;
    cursor: pointer;
    box-shadow: 0 6px 16px var(--db-shadow);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9998;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.daebook-launcher-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px var(--db-shadow);
}

.daebook-launcher-btn svg {
    color: var(--db-text-light);
    width: 24px;
    height: 24px;
}

/* Chat Window */
.daebook-chat-window {
    position: fixed;
    bottom: 96px;
    right: 24px;
    width: 360px;
    height: 550px;
    max-height: calc(100vh - 120px);
    background: var(--db-bg-medium);
    border-radius: 12px;
    box-shadow: 0 12px 40px var(--db-shadow);
    display: none;
    flex-direction: column;
    z-index: 9999;
    overflow: hidden;
    animation: daebookSlideUp 0.3s ease-out;
    border: 1px solid var(--db-border);
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
    background: linear-gradient(135deg, var(--db-primary-dark) 0%, var(--db-accent) 100%);
    color: var(--db-text-light);
    padding: 14px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
}

.daebook-chat-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
}

.daebook-chat-icon {
    font-size: 18px;
}

.daebook-close-btn {
    background: transparent;
    border: none;
    color: var(--db-text-light);
    font-size: 20px;
    line-height: 1;
    cursor: pointer;
    padding: 4px;
    border-radius: 6px;
    transition: background 0.2s;
    display: flex;
    align-items: center;
}

.daebook-close-btn:hover {
    background: rgba(255, 255, 255, 0.15);
}

/* Messages Container */
.daebook-chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    background: var(--db-bg-dark);
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* Custom Scrollbar */
.daebook-chat-messages::-webkit-scrollbar {
    width: 4px;
}

.daebook-chat-messages::-webkit-scrollbar-track {
    background: var(--db-bg-dark);
}

.daebook-chat-messages::-webkit-scrollbar-thumb {
    background: var(--db-primary-dark);
    border-radius: 2px;
}

.daebook-chat-messages::-webkit-scrollbar-thumb:hover {
    background: var(--db-primary-light);
}

/* Messages */
.daebook-message {
    max-width: 80%;
    padding: 10px 14px;
    border-radius: 12px;
    line-height: 1.4;
    word-wrap: break-word;
    font-size: 14px;
    animation: daebookFadeIn 0.3s ease-out;
    margin-right: 8px;
    margin-left: 8px;
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

/* User Messages (Right) */
.daebook-message.daebook-user {
    align-self: flex-end;
    background: linear-gradient(135deg, var(--db-primary-dark) 0%, var(--db-primary-light) 100%);
    color: var(--db-text-light);
    border-bottom-right-radius: 4px;
    font-weight: 500;
    margin-left: auto;
}

/* Bot Messages (Left) */
.daebook-message.daebook-bot {
    align-self: flex-start;
    background: var(--db-bg-light);
    color: var(--db-text-light);
    border: 1px solid var(--db-border);
    border-bottom-left-radius: 4px;
    margin-right: auto;
}

/* Welcome Message specific styling */
.daebook-message.daebook-welcome {
    margin-bottom: 10px;
}

/* Typing Indicator */
.daebook-message.daebook-typing {
    align-self: flex-start;
    background: var(--db-bg-light);
    border: 1px solid var(--db-border);
    color: var(--db-primary-light);
    padding: 10px 14px;
    width: 60px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: auto;
}

@keyframes typing-dots {
    0% { transform: translateX(0) scale(1); opacity: 0.5; }
    33% { transform: translateX(15px) scale(1.1); opacity: 1; }
    66% { transform: translateX(30px) scale(1); opacity: 0.5; }
    100% { transform: translateX(0) scale(1); opacity: 0.5; }
}

.daebook-typing::after {
    content: '';
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--db-primary-light);
    box-shadow: 15px 0 0 0 var(--db-primary-light), 30px 0 0 0 var(--db-primary-light);
    animation: typing-dots 1.5s infinite ease-in-out;
}

/* Input Area */
.daebook-chat-input-area {
    display: flex;
    padding: 12px 16px;
    background: var(--db-bg-medium);
    border-top: 1px solid var(--db-border);
    gap: 8px;
}

.daebook-chat-input {
    flex: 1;
    border: 2px solid var(--db-border);
    border-radius: 20px;
    padding: 8px 14px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    background: var(--db-bg-dark);
    color: var(--db-text-light);
    resize: none;
}

.daebook-chat-input::placeholder {
    color: rgba(255, 255, 255, 0.5);
    font-weight: 300;
}

.daebook-chat-input:focus {
    border-color: var(--db-primary-light);
    box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.3);
}

.daebook-send-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--db-primary-dark) 0%, var(--db-primary-light) 100%);
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s, opacity 0.2s;
    flex-shrink: 0;
}

.daebook-send-btn:hover:not(:disabled) {
    transform: scale(1.05);
}

.daebook-send-btn:active:not(:disabled) {
    transform: scale(0.95);
}

.daebook-send-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background: var(--db-bg-dark);
    border: 1px solid var(--db-border);
}

.daebook-send-btn svg {
    color: var(--db-text-light);
}

/* Mobile Responsive */
@media (max-width: 480px) {
    .daebook-chat-window {
        width: calc(100vw - 32px);
        height: calc(100vh - 80px);
        bottom: 16px;
        right: 16px;
        max-height: calc(100vh - 80px);
    }
    
    .daebook-launcher-btn {
        bottom: 16px;
        right: 16px;
    }
    
    .daebook-chat-window {
        border-radius: 12px;
    }
}
</style>

<script>
(function() {
    // ==================== KONFIGURASI ====================
    // PERUBAHAN PENTING: Gunakan path absolut dari root agar selalu menunjuk ke file yang benar
    const API_URL = '/chat.php?action=chat'; 
    
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
        "Halo kak! Saya Dea, asisten DaeBook 🤖\n\nMau cari buku, cek stok, atau tanya harga?\nLangsung ketik aja ya!",
        "Selamat datang di DaeBook! 🌟\n\nAda yang bisa Dea bantu hari ini? Cari buku, cek harga, atau tanya stok?",
        "Haii! Dea siap bantu kakak nyari buku impian 📚\n\nKetik judul, kode, atau langsung tanya stok ya!",
        "Halo! Selamat berbelanja di DaeBook 🛍️\n\nDea di sini siap bantu cek stok dan harga buku favoritmu!"
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
        messagesContainer.scrollTo({
            top: messagesContainer.scrollHeight,
            behavior: 'smooth'
        });
    }
    
    function addMessage(text, type, id = null) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `daebook-message daebook-${type}`;
        if (id) {
             msgDiv.id = id;
        }
        msgDiv.innerHTML = escapeHtml(text).replace(/\n/g, '<br>');
        messagesContainer.appendChild(msgDiv);
        scrollToBottom();
        return msgDiv;
    }
    
    async function sendMessage() {
        if (isSending) return;
        
        const message = inputField.value.trim();
        if (!message) return;
        
        addMessage(message, 'user');
        inputField.value = '';
        isSending = true;
        sendBtn.disabled = true;
        
        const typingDiv = addMessage('', 'typing', 'daebook-typing-indicator');
        
        try {
            console.log('Sending request to:', API_URL); // Tambahkan log untuk debugging
            const res = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ prompt: message })
            });
            
            const data = await res.json();
            
            typingDiv.remove();
            
            if (data.result) {
                addMessage(data.result, 'bot');
            } else if (data.error) {
                addMessage('❌ ' + data.error, 'bot');
            } else {
                addMessage('⚠️ Maaf, respon server tidak dapat diproses.', 'bot');
            }
        } catch (err) {
            typingDiv.remove();
            addMessage('🚨 Gagal nyambung ke server. Pastikan server Node.js di port 3000 lagi jalan ya!', 'bot');
            console.error('Fetch Error:', err); // Tambahkan log untuk debugging
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

    if (welcomeMsg.innerHTML.includes('memuat sambutan')) {
        welcomeMsg.innerHTML = escapeHtml(randomWelcome).replace(/\n/g, '<br>');
    }
    
})();
</script>