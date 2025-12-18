<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$db_name = 'pos_daebook';
$username = 'root';
$password = '';
$filesql = '/database/pos_daebook.sql';

function render_form($message = '') {
    $msgHtml = $message 
        ? "<div style='padding:12px;background:#eaf6ff;border:1px solid #b6daff;margin-bottom:16px;border-radius:6px;'>$message</div>" 
        : "";

    echo <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo/logo.png">
    <title>Instalasi Database</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f5f7fa;
        padding: 20px;
    }
    h1 {
        margin-bottom: 20px;
        color: #1f2937;
    }
    form {
        background: #ffffff;
        padding: 20px;
        border-radius: 10px;
        border: 1px solid #e4e4e7;
        max-width: 420px;
    }
    select {
        width: 100%;
        padding: 10px;
        border-radius: 6px;
        border: 1px solid #d1d5db;
        margin-top: 6px;
        background: #fff;
    }
    button {
        padding: 10px 16px;
        border-radius: 6px;
        background: #4b5563;
        color: white;
        border: none;
        cursor: pointer;
        margin-top: 16px;
    }
    button:hover {
        background: #374151;
    }
    hr {
        margin: 24px 0;
    }
</style>
</head>
<body>

<h1>Instalasi Database POS Daebook</h1>
{$msgHtml}

<form method="POST" onsubmit="return confirmAction()">
    <label for="action"><strong>Pilih aksi:</strong></label>
    <select name="action" id="action" required>
        <option value="">-- Pilih --</option>
        <option value="install">Install DB (buat & import schema)</option>
        <option value="restart">Restart DB (kosongkan kecuali 'users' & 'pengaturan')</option>
        <option value="hapus">Hapus DB (drop database)</option>
    </select>

    <button type="submit">Jalankan</button>
</form>

<script>
function confirmAction(){
    const a = document.getElementById('action').value;

    if(a === 'install'){
        return confirm('Install: Membuat database jika belum ada dan mengimpor schema. Lanjutkan?');
    }
    if(a === 'restart'){
        return confirm('Restart: Menghapus seluruh data kecuali tabel users dan pengaturan. Pastikan sudah backup. Lanjutkan?');
    }
    if(a === 'hapus'){
        return confirm('Hapus: Database akan DIHAPUS PERMANEN beserta semua tabel. Tindakan ini tidak dapat dibatalkan. Lanjutkan?');
    }
    return false;
}
</script>

<hr>

<p><small>File ini dapat dihapus setelah proses instalasi atau maintenance selesai.</small></p>

</body>
</html>
HTML;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    render_form();
    exit;
}

$action = $_POST['action'] ?? '';

try {

    // INSTALL
    if ($action === 'install') {

        $pdo = new PDO("mysql:host=$host", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        $pdo->exec("USE `$db_name`");

        $schemaFile = __DIR__ . $filesql ;
        if (!file_exists($schemaFile)) {
            throw new Exception('File schema tidak ditemukan: ' . $schemaFile);
        }

        $schema = file_get_contents($schemaFile);
        $statements = array_filter(array_map('trim', preg_split('/;[\r\n]+/', $schema)));

        foreach ($statements as $sql) {
            if ($sql !== '') {
                try { $pdo->exec($sql); }
                catch (PDOException $e) { error_log('SQL import warning: ' . $e->getMessage()); }
            }
        }

        $test = new PDO("mysql:host=$host;dbname=$db_name", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $count = 0;
        try {
            $q = $test->query("SELECT COUNT(*) AS c FROM users");
            $row = $q->fetch(PDO::FETCH_ASSOC);
            $count = $row['c'] ?? 0;
        } catch (Exception $e) {}

        render_form("Install selesai. Jumlah user terdeteksi: {$count}.");
        exit;
    }

    // RESTART
    if ($action === 'restart') {

        $pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $preserve = ['users', 'pengaturan'];

        $stmt = $pdo->prepare("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = :db");
        $stmt->execute([':db' => $db_name]);
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!$tables) {
            render_form("Tidak ada tabel ditemukan di database {$db_name}.");
            exit;
        }

        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

        $truncated = [];
        $dropped = [];

        foreach ($tables as $t) {
            if (in_array($t, $preserve)) continue;

            try {
                $pdo->exec("TRUNCATE TABLE `$t`");
                $truncated[] = $t;
            } catch (PDOException $e) {
                error_log("Truncate failed for {$t}: " . $e->getMessage());
                try {
                    $pdo->exec("DROP TABLE IF EXISTS `$t`");
                    $dropped[] = $t;
                } catch (PDOException $ex) {
                    error_log("Drop failed for {$t}: " . $ex->getMessage());
                }
            }
        }

        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        render_form(
            "Restart selesai.<br><strong>Tabel dikosongkan:</strong> " . implode(', ', $truncated ?: ['-']) .
            "<br><strong>Tabel di-drop:</strong> " . implode(', ', $dropped ?: ['-'])
        );
        exit;
    }

    // HAPUS
    if ($action === 'hapus') {

        $pdo = new PDO("mysql:host=$host", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $pdo->exec("DROP DATABASE IF EXISTS `$db_name`");

        render_form("Database '$db_name' berhasil dihapus.");
        exit;
    }

    throw new Exception('Aksi tidak dikenal.');

} catch (Exception $e) {
    render_form("<span style='color:red;'>Error: " . htmlspecialchars($e->getMessage()) . "</span>");
    exit;
}
?>
