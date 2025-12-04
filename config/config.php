<?php
// Konfigurasi umum aplikasi
define('BASE_URL', 'http://localhost/daebook/');
define('APP_NAME', 'Daebook');

// Lingkungan: 'development' atau 'production'
define('APP_ENV', 'development');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

session_start();

spl_autoload_register(function ($class_name) {
    $base_dir = dirname(__DIR__) . '/';
    $directories = [
        'classes/',
        'models/',
        'controllers/'
    ];

    foreach ($directories as $directory) {
        $file = $base_dir . $directory . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once __DIR__ . '/database.php';

function sanitizeInput($v)
{
    if (is_array($v)) {
        return array_map('sanitizeInput', $v);
    }
    return trim(htmlspecialchars((string)$v, ENT_QUOTES));
}

function isLoggedIn()
{
    return !empty($_SESSION['user_id']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireRole(array $roles = [])
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    if (!empty($roles) && !in_array($_SESSION['user_role'] ?? '', $roles)) {
        header('Location: unauthorized.php');
        exit;
    }
}

function formatCurrency($v)
{
    return 'Rp ' . number_format((float)$v, 0, ',', '.');
}
