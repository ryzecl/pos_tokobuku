<?php
require_once 'config/config.php';

require_once 'models/Penjualan.php';
require_once 'models/Customer.php';
require_once 'models/Pengaturan.php';

$database = new Database();
$db = $database->getConnection();

$penjualan = new Penjualan($db);
$customer = new Customer($db);
$pengaturan = new Pengaturan($db);

// ✅ PERBAIKAN: Terima token dari URL, bukan ID
$token = $_GET['token'] ?? '';

// Validasi token
if (empty($token) || strlen($token) !== 64 || !ctype_xdigit($token)) {
    http_response_code(400);
    die('Token tidak valid.');
}

// Cari struk berdasarkan token
try {
    $stmt = $db->prepare("SELECT * FROM penjualan WHERE token_public = :token LIMIT 1");
    $stmt->bindValue(':token', $token, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        http_response_code(404);
        die('Struk tidak ditemukan.');
    }

    // Isi object penjualan dari row
    $penjualan->id = $row['id'];
    $penjualan->no_transaksi = $row['no_transaksi'];
    $penjualan->user_id = $row['user_id'];
    $penjualan->customer_id = $row['customer_id'];
    $penjualan->diskon = $row['diskon'];
    $penjualan->ppn = $row['ppn'];
    $penjualan->total_harga = $row['total_harga'];
    $penjualan->total_bayar = $row['total_bayar'];
    $penjualan->kembalian = $row['kembalian'];
    $penjualan->tanggal_penjualan = $row['tanggal_penjualan'];
    $penjualan->metode_pembayaran = $row['metode_pembayaran'];
    $penjualan->note = $row['note'] ?? null;
    $penjualan->token_public = $token;

} catch (Exception $e) {
    http_response_code(500);
    die('Error: ' . $e->getMessage());
}

// Ambil pengaturan toko
$nama_toko = $pengaturan->get('nama_toko') ?? APP_NAME;
$alamat_toko = $pengaturan->get('alamat_toko') ?? '';
$telepon_toko = $pengaturan->get('telepon_toko') ?? '';
$email_toko = $pengaturan->get('email_toko') ?? '';
$ppn_persen = floatval($pengaturan->get('ppn_persen') ?? 10);
$footer_struk = $pengaturan->get('footer_struk') ?? 'Terima kasih atas kunjungan Anda!';

// Customer
$customer_name = '';
if (!empty($penjualan->customer_id)) {
    $customer->id = $penjualan->customer_id;
    if ($customer->readOne()) {
        $customer_name = $customer->nama_customer;
    }
}

// URL publik struk ini
$struk_url = BASE_URL . "struk.php?token=" . urlencode($token);

// QR Code: link ke struk
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($struk_url);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Penjualan - <?php echo htmlspecialchars($nama_toko); ?></title>

    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            background: white;
            padding: 20px;
        }

        .receipt {
            width: 300px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #000;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo {
            width: 70px;
            height: auto;
            margin-bottom: 5px;
        }

        .header h1 {
            font-size: 16px;
            margin: 0;
        }

        .header p {
            margin: 0;
            font-size: 10px;
            line-height: 1.3;
        }

        .divider {
            border-bottom: 1px dashed #000;
            margin: 10px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }

        .item-header,
        .item-row {
            display: grid;
            grid-template-columns: 3fr 1fr 1fr 1.2fr;
            font-size: 11px;
            gap: 5px;
        }

        .item-header {
            font-weight: bold;
            border-bottom: 1px solid black;
            padding-bottom: 3px;
        }

        .item-row {
            margin-top: 3px;
        }

        .summary {
            margin-top: 10px;
        }

        .summary .row.total {
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 10px;
        }

        .qr-wrap {
            text-align: center;
            margin-top: 10px;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
            }

            .no-print {
                display: none;
            }

            .receipt {
                border: none;
                width: 100%;
                max-width: 300px;
            }
        }

        .btn {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
    </style>
</head>
<body>

    <!-- Tombol admin hanya muncul jika login -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <div class="no-print" style="text-align:center; margin-bottom:20px;">
        <button onclick="window.print()" class="btn">Cetak Struk</button>
        <a href="penjualan.php" class="btn">Transaksi Baru</a>
        <a href="dashboard.php" class="btn">Dashboard</a>
    </div>
    <?php endif; ?>

    <div class="receipt">

        <div class="header">
            <?php if (file_exists("assets/logo.jpg")): ?>
                <img src="assets/logo.jpg" class="logo" alt="Logo">
            <?php endif; ?>

            <h1><?php echo htmlspecialchars($nama_toko); ?></h1>

            <p>
                <?php if ($alamat_toko) echo htmlspecialchars($alamat_toko) . "<br>"; ?>
                <?php if ($telepon_toko) echo "Telp: " . htmlspecialchars($telepon_toko) . "<br>"; ?>
                <?php if ($email_toko) echo "Email: " . htmlspecialchars($email_toko); ?>
            </p>
        </div>

        <div class="divider"></div>

        <div class="row">
            <span>No Transaksi:</span>
            <span><?php echo htmlspecialchars($penjualan->no_transaksi); ?></span>
        </div>

        <div class="row">
            <span>Tanggal:</span>
            <span><?php echo date('d/m/Y H:i:s', strtotime($penjualan->tanggal_penjualan)); ?></span>
        </div>

        <?php if ($customer_name): ?>
            <div class="row">
                <span>Customer:</span>
                <span><?php echo htmlspecialchars($customer_name); ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="row">
            <span>Kasir:</span>
            <span><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
        </div>
        <?php endif; ?>

        <div class="row">
            <span>Metode:</span>
            <span><?php echo htmlspecialchars($penjualan->metode_pembayaran); ?></span>
        </div>

        <div class="divider"></div>

        <div class="item-header">
            <span>Item</span><span>Qty</span><span>Harga</span><span>Total</span>
        </div>

        <?php
        $subtotal = 0;
        $detail_stmt = $penjualan->getDetailPenjualan($penjualan->id);

        while ($row = $detail_stmt->fetch(PDO::FETCH_ASSOC)):
            $item_diskon = floatval($row['diskon'] ?? 0);
            $bef = $row['harga_satuan'] * $row['jumlah'];
            $aft = $bef - $item_diskon;
            $subtotal += $aft;
        ?>

            <div class="item-row">
                <span><?php echo htmlspecialchars($row['nama_buku']); ?></span>
                <span><?php echo $row['jumlah']; ?></span>
                <span><?php echo number_format($row['harga_satuan'], 0, ',', '.'); ?></span>
                <span><?php echo number_format($aft, 0, ',', '.'); ?></span>
            </div>

        <?php endwhile; ?>

        <div class="divider"></div>

        <div class="summary">
            <div class="row">
                <span>Subtotal:</span>
                <span>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></span>
            </div>

            <?php if ($penjualan->diskon > 0): ?>
                <div class="row">
                    <span>Diskon:</span>
                    <span>Rp <?php echo number_format($penjualan->diskon, 0, ',', '.'); ?></span>
                </div>
            <?php endif; ?>

            <div class="row">
                <span>PPN (<?php echo $ppn_persen; ?>%):</span>
                <span>Rp <?php echo number_format($penjualan->ppn, 0, ',', '.'); ?></span>
            </div>

            <div class="row total">
                <span>Total:</span>
                <span>Rp <?php echo number_format($penjualan->total_harga, 0, ',', '.'); ?></span>
            </div>

            <div class="row">
                <span>Bayar:</span>
                <span>Rp <?php echo number_format($penjualan->total_bayar, 0, ',', '.'); ?></span>
            </div>

            <div class="row">
                <span>Kembalian:</span>
                <span>Rp <?php echo number_format($penjualan->kembalian, 0, ',', '.'); ?></span>
            </div>
        </div>

        <?php if (!empty($penjualan->note)): ?>
            <div class="divider"></div>
            <div>
                <strong>Catatan:</strong><br>
                <?php echo htmlspecialchars($penjualan->note); ?>
            </div>
        <?php endif; ?>

        <div class="qr-wrap">
            <img src="<?php echo htmlspecialchars($qr_url); ?>" width="130" alt="QR Code">
            <div style="font-size:10px; margin-top:5px;">Scan untuk buka struk</div>
        </div>

        <div class="footer">
            <p><?php echo htmlspecialchars($footer_struk); ?></p>
            <p>Buku yang sudah dibeli tidak dapat dikembalikan</p>
        </div>
    </div>

</body>
</html>