<?php
require_once 'config/config.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    header('HTTP/1.1 302 Found');
    header('Location: dashboard.php');
    exit();
}

// Inisialisasi CSRF token (jika belum ada)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Inisialisasi percobaan login di session
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = 0;
}

$error_message = '';
$show_demo = (defined('APP_ENV') && APP_ENV === 'development');

// Cooldown: 5 menit setelah 3x gagal
$cooldown_seconds = 300;
$now = time();

// Cek cooldown
if ($_SESSION['login_attempts'] >= 3 && ($now - $_SESSION['last_attempt_time']) < $cooldown_seconds) {
    $remaining = $cooldown_seconds - ($now - $_SESSION['last_attempt_time']);
    $minutes = ceil($remaining / 60);
    $error_message = "Terlalu banyak percobaan gagal. Silakan coba lagi dalam {$minutes} menit.";
} elseif ($_POST) {
    // Validasi CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die('Invalid request.');
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_message = 'Username dan password harus diisi!';
    } else {
        // Delay 1 detik untuk hambat bruteforce
        sleep(1);

        $database = new Database();
        $db = $database->getConnection();
        $user = new User($db);

        if ($user->login($username, $password)) {
            // 🔒 Regenerasi session ID (anti session fixation)
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            $_SESSION['nama_lengkap'] = $user->nama_lengkap;
            $_SESSION['user_role'] = $user->role;
            $_SESSION['email'] = $user->email;

            // Reset percobaan gagal
            $_SESSION['login_attempts'] = 0;
            $_SESSION['last_attempt_time'] = 0;

            header('HTTP/1.1 302 Found');
            header('Location: dashboard.php');
            exit();
        } else {
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = time();
            $error_message = 'Username atau password salah!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dynamic.php">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1><?php echo htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></h1>
                <p>Silakan masuk dengan akun Anda</p>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8') : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-full">Masuk</button>
            </form>

            <?php if ($show_demo): ?>
            <div class="login-footer">
                <p><strong>Demo Account:</strong></p>
                <p>Admin: <code>admin</code> / password</p>
                <p>Kasir: <code>kasir1</code> / password</p>
                <p>Gudang: <code>gudang1</code> / password</p>
                <p style="margin-top: 15px;">
                    <a href="index.php" style="color: #007bff; text-decoration: none;">← Kembali ke Beranda</a>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>