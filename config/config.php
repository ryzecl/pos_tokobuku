<?php
// Konfigurasi umum aplikasi
define('BASE_URL', 'http://localhost/pos_daebook/');
define('APP_NAME', 'Daebook');

// Konfigurasi session
session_start();

// Autoload classes
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

// Include database connection
// Include database connection
require_once __DIR__ . '/database.php';

// Helper functions
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireRole($allowedRoles)
{
    requireLogin();
    if (!in_array($_SESSION['user_role'], $allowedRoles)) {
        header('Location: unauthorized.php');
        exit();
    }
}

function sanitizeInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function formatCurrency($amount)
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}
