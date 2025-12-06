<?php
/**
 * DaeBook Chat Popup - Backend API Handler
 * File ini hanya handle POST request untuk chat API
 * Untuk UI widget, gunakan chat-widget.php
 */

const API_URL = 'http://localhost:3000/generate-text';
const MAX_MESSAGE_LENGTH = 1500;
const RATE_LIMIT_SECONDS = 2;

// ==================== FUNGSI KEAMANAN ====================

function escapeHtml($text) {
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function checkRateLimit($ip) {
    $rateFile = sys_get_temp_dir() . '/daebook_rate_' . md5($ip) . '.txt';
    
    if (file_exists($rateFile)) {
        $lastRequest = (int) file_get_contents($rateFile);
        $timeDiff = time() - $lastRequest;
        
        if ($timeDiff < RATE_LIMIT_SECONDS) {
            return false;
        }
    }
    
    file_put_contents($rateFile, time());
    return true;
}

function validatePrompt($prompt) {
    if (empty($prompt) || !is_string($prompt)) {
        return ['valid' => false, 'error' => 'Prompt tidak boleh kosong!'];
    }
    
    if (strlen($prompt) > MAX_MESSAGE_LENGTH) {
        return ['valid' => false, 'error' => 'Pesan terlalu panjang, maksimal ' . MAX_MESSAGE_LENGTH . ' karakter ya.'];
    }
    
    $prompt = trim($prompt);
    
    if (empty($prompt)) {
        return ['valid' => false, 'error' => 'Prompt tidak boleh kosong!'];
    }
    
    return ['valid' => true, 'prompt' => $prompt];
}

// ==================== BACKEND API HANDLER ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    if (!checkRateLimit($clientIp)) {
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'error' => 'Terlalu banyak permintaan. Tunggu sebentar ya! 😊'
        ]);
        exit;
    }
    
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
    
    $validation = validatePrompt($prompt);
    if (!$validation['valid']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $validation['error']
        ]);
        exit;
    }
    
    $cleanPrompt = $validation['prompt'];
    
    try {
        $postData = json_encode(['prompt' => $cleanPrompt]);
        
        $ch = curl_init(API_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postData)
            ]
        ]);
        
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
        
        if (isset($responseData['result'])) {
            $responseData['result'] = escapeHtml($responseData['result']);
        }
        
        if (isset($responseData['error'])) {
            $responseData['error'] = escapeHtml($responseData['error']);
        }
        
        if (isset($responseData['result'])) {
            $responseData['success'] = true;
        } else {
            $responseData['success'] = false;
        }
        
        echo json_encode($responseData);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Maaf, saat ini sedang ada gangguan. Silakan coba lagi beberapa saat lagi. 🙏'
        ]);
        
        error_log('DaeBook Chat Error: ' . $e->getMessage());
    }
    
    exit;
}

// Jika bukan POST, return error
http_response_code(405);
echo json_encode([
    'success' => false,
    'error' => 'Method not allowed. Use POST to send messages.'
]);
