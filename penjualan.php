<?php
require_once 'config/config.php';
requireRole(['admin', 'kasir']);

require_once 'models/Buku.php';
require_once 'models/Penjualan.php';
require_once 'models/Customer.php';
require_once 'models/Pengaturan.php';

$database = new Database();
$db = $database->getConnection();

// 🔥 Pastikan $db valid dan dikirim ke model
$buku = new Buku($db);
$penjualan = new Penjualan($db); // ← ini wajib!
$customer = new Customer($db);
$pengaturan = new Pengaturan($db);

$ppn_persen = floatval($pengaturan->get('ppn_persen') ?? 10);

$message = '';
$message_type = '';

$role = $_SESSION['user_role'] ?? 'kasir';

// ---- AJAX handlers ----
if (isset($_GET['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    switch ($_GET['action']) {
        case 'search_buku':
            $keyword = sanitizeInput($_GET['keyword'] ?? '');
            
            // Jika keyword kosong, tampilkan SEMUA buku
            if (empty($keyword)) {
                $stmt = $buku->readAll();
            } else {
                $stmt = $buku->search($keyword);
            }
            
            $results = [];
            if ($stmt instanceof PDOStatement) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $results[] = [
                        'id' => isset($row['id']) ? (int)$row['id'] : 0,
                        'kode_buku' => $row['kode_buku'] ?? '',
                        'nama_buku' => $row['nama_buku'] ?? '',
                        'harga_jual' => isset($row['harga_jual']) ? (float)$row['harga_jual'] : 0,
                        'diskon' => isset($row['diskon']) ? (float)$row['diskon'] : 0,
                        'stok' => isset($row['stok']) ? (int)$row['stok'] : 0,
                        'satuan' => $row['satuan'] ?? 'pcs'
                    ];
                }
            }
            echo json_encode($results);
            exit;

        case 'get_buku':
            $buku_id = (int)sanitizeInput($_GET['buku_id'] ?? 0);
            $buku->id = $buku_id;
            if ($buku_id > 0 && $buku->readOne()) {
                echo json_encode([
                    'id' => (int)$buku->id,
                    'kode_buku' => $buku->kode_buku,
                    'nama_buku' => $buku->nama_buku,
                    'harga_jual' => (float)$buku->harga_jual,
                    'diskon' => (float)($buku->diskon ?? 0),
                    'stok' => (int)$buku->stok,
                    'satuan' => $buku->satuan
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'buku tidak ditemukan']);
            }
            exit;
    }
}

// ---- PROSES TRANSAKSI ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process_transaction') {
    try {
        $db->beginTransaction();

        // Generate nomor transaksi yang pasti string
        $no_transaksi = $penjualan->generateNoTransaksi();
        if (!is_string($no_transaksi) || strlen($no_transaksi) < 10) {
            throw new Exception('Gagal menghasilkan nomor transaksi yang valid.');
        }

        $penjualan->no_transaksi = $no_transaksi;
        $penjualan->user_id = $_SESSION['user_id'] ?? null;
        $penjualan->customer_id = !empty($_POST['customer_id']) ? sanitizeInput($_POST['customer_id']) : null;
        $penjualan->diskon = floatval($_POST['diskon'] ?? 0);
        $penjualan->ppn = floatval($_POST['ppn'] ?? 0);
        $penjualan->total_harga = floatval($_POST['total_harga'] ?? 0);
        $penjualan->total_bayar = floatval($_POST['total_bayar'] ?? 0);
        $penjualan->kembalian = floatval($_POST['kembalian'] ?? 0);
        $penjualan->note = !empty($_POST['note']) ? sanitizeInput($_POST['note']) : null;
        $penjualan->metode_pembayaran = $_POST['metode_pembayaran'] ?? 'CASH'; // ✅ aman

        // Ambil item
        $itemsRaw = $_POST['items'] ?? '';
        $items = json_decode($itemsRaw, true);
        if ($items === null) {
            $items = json_decode(html_entity_decode(stripslashes($itemsRaw)), true);
        }
        if (!is_array($items) || empty($items)) {
            throw new Exception('Detail transaksi tidak valid.');
        }

        // Validasi stok & siapkan detail
        $details = [];
        foreach ($items as $it) {
            $buku_id = (int)($it['id'] ?? $it['buku_id'] ?? 0);
            $qty = (int)($it['quantity'] ?? $it['qty'] ?? 0);
            $price = (float)($it['price'] ?? $it['harga'] ?? 0);
            $subtotal = (float)($it['subtotal'] ?? max(0, $price * $qty));

            if ($buku_id <= 0 || $qty <= 0) continue;

            $buku->id = $buku_id;
            if (!$buku->readOne()) {
                throw new Exception("Buku ID {$buku_id} tidak ditemukan.");
            }
            if ($buku->stok < $qty) {
                throw new Exception("Stok '{$buku->nama_buku}' tidak cukup.");
            }

            $details[] = compact('buku_id', 'qty', 'price', 'subtotal');
        }

        if (empty($details)) {
            throw new Exception('Tidak ada item valid dalam keranjang.');
        }

        // Simpan header
        if (!$penjualan->create()) {
            throw new Exception('Gagal menyimpan transaksi.');
        }

        $penjualan_id = $penjualan->id;
        if (!$penjualan_id) {
            throw new Exception('ID transaksi tidak tersedia setelah insert.');
        }

        // Simpan detail & update stok
        $detailStmt = $db->prepare(
            "INSERT INTO detail_penjualan (penjualan_id, buku_id, jumlah, harga_satuan, subtotal) 
             VALUES (:pid, :bid, :qty, :price, :subtotal)"
        );

        foreach ($details as $d) {
            $detailStmt->execute([
                ':pid' => $penjualan_id,
                ':bid' => $d['buku_id'],
                ':qty' => $d['qty'],
                ':price' => $d['price'],
                ':subtotal' => $d['subtotal']
            ]);

            $buku->updateStok($d['buku_id'], -$d['qty']);
        }

        // 🔥 Generate token publik untuk struk
        $token = bin2hex(random_bytes(32)); // 64 karakter hex
        $updateToken = $db->prepare("UPDATE penjualan SET token_public = :token WHERE id = :id");
        $updateToken->execute([':token' => $token, ':id' => $penjualan_id]);

        $db->commit();
        header('Location: struk.php?token=' . urlencode($token));
        exit();

    } catch (Exception $e) {
        $db->rollBack();
        $message = 'Gagal: ' . $e->getMessage();
        $message_type = 'error';
    }
}

$customer_stmt = $customer->readAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo/logo1.png">
    <title>Penjualan - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dynamic.php">
    <style>
        .pos-container { display: grid; grid-template-columns: 1fr 420px; gap: 20px; height: calc(100vh - 120px); }
        .product-section, .cart-section { background: #0f0f1a; border-radius: 16px; box-shadow: 0 0 8px 25px rgba(162, 89, 255, 0.1); padding: 24px; }
        .search-box input { width: 100%; padding: 14px; border: 2px solid #2a2a3e; border-radius: 12px; font-size: 16px; background: #1a1a2e; color: #e0e0e0; }
        .search-box input:focus { outline: none; border-color: #A259FF; background: #1a1a2e; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 16px; }
        .product-card { border: 2px solid #2a2a3e; border-radius: 14px; padding: 10px; cursor: pointer; transition: all 0.3s; background: #1a1a2e; }
        .product-card:hover { border-color: #A259FF; transform: translateY(-4px); box-shadow: 0 8px 20px rgba(162, 89, 255, 0.2); }
        .product-name { font-weight: 700; color: #e0e0e0; margin-bottom: 6px; }
        .product-price { color: #A259FF; font-weight: bold; font-size: 18px; }
        .product-stock { font-size: 13px; color: #9f7aea; }
        .cart-items { flex: 1; overflow-y: auto; margin-bottom: 20px; padding-right: 8px; }
        .cart-summary { border-top: 3px solid #A259FF; padding-top: 18px; background: #1a1a2e; border-radius: 12px; padding: 18px; margin-bottom: 16px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px; color: #e0e0e0; }
        .summary-row.total { font-weight: bold; font-size: 20px; color: #A259FF; padding-top: 12px; border-top: 1px dashed #A259FF; }
        .payment-section input, .payment-section select { width: 100%; padding: 14px; border: 2px solid #2a2a3e; border-radius: 12px; margin-bottom: 12px; font-size: 15px; background: #1a1a2e; color: #e0e0e0; }
        .payment-section input:focus, .payment-section select:focus { outline: none; border-color: #A259FF; background: #1a1a2e; color: #e0e0e0; }
        .payment-section input::placeholder { color: #6b6b8a; }
        .payment-section label { color: #a0a0c0; font-size: 14px; font-weight: 600; display: block; margin-bottom: 8px; }
        .btn-process { width: 100%; padding: 18px; background: linear-gradient(135deg, #A259FF, #9041E0); color: white; border: none; border-radius: 14px; font-size: 18px; font-weight: bold; cursor: pointer; transition: all 0.3s; box-shadow: 0 6px 20px rgba(162, 89, 255, 0.3); }
        .btn-process:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(162, 89, 255, 0.4); }
        .btn-process:disabled { background: #3a3a4e; cursor: not-allowed; color: #7a7a9e; }
    </style>
</head>
<body>
    <div class="main-container">
        <?php require_once 'components/sidebar.php'; ?>
        <main class="main-content">
            <header class="top-nav">
                <h1>Point of Sale (POS)</h1>
                <div class="user-info">
                    <div class="user-avatar" style="background: linear-gradient(135deg, #A259FF, #9041E0);"><?php echo strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)); ?></div>
                    <div class="user-details">
                        <div class="user-name"><?php echo $_SESSION['nama_lengkap']; ?></div>
                        <div class="user-role"><?php echo ucfirst($_SESSION['user_role']); ?></div>
                    </div>
                </div>
            </header>

            <div class="content">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <div class="pos-container">
                    <div class="product-section">
                        <div class="search-box">
                            <input type="text" id="searchInput" placeholder="Cari buku..." onkeyup="searchProducts()">
                        </div>
                        <div id="productGrid" class="product-grid"></div>
                    </div>

                    <div class="cart-section">
                        <h3 style="margin:0 0 20px 0; color:#A259FF; font-size:22px;">Keranjang Belanja</h3>
                        <div class="cart-items" id="cartItems">
                            <div style="text-align:center;padding:60px 20px;color:#6b6b8a;">
                                <div style="font-size:80px;margin-bottom:20px;opacity:0.3;">🛒</div>
                                <p style="font-size:18px;font-weight:600;margin:0;color:#e0e0e0;">Keranjang Kosong</p>
                                <small style="color:#7a7a9e;">Pilih buku untuk memulai transaksi</small>
                            </div>
                        </div>

                        <div class="cart-summary">
                            <div class="summary-row"><span>Subtotal:</span><span id="subtotal">Rp 0</span></div>
                            <div class="summary-row"><span>Diskon:</span><span id="diskonDisplay">Rp 0</span></div>
                            <div class="summary-row"><span>PPN (<?php echo $ppn_persen; ?>%):</span><span id="ppn">Rp 0</span></div>
                            <div class="summary-row total"><span>Total:</span><span id="total">Rp 0</span></div>
                        </div>

                        <div class="payment-section">
                            <select id="customer_id">
                                <option value="">Pilih Customer (Opsional)</option>
                                <?php while ($row = $customer_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['nama_customer']); ?></option>
                                <?php endwhile; ?>
                            </select>
                            <input type="number" id="diskonInput" placeholder="Diskon Transaksi (Rp)" value="0" min="0" onkeyup="updateSummary()">
                            
                            <label for="metode_pembayaran">Metode Pembayaran</label>
                            <select id="metode_pembayaran" required>
                                <option value="CASH">CASH</option>
                                <option value="QRIS">QRIS</option>
                                <option value="TRANSFER">TRANSFER</option>
                            </select>
                            
                            <input type="number" id="paymentInput" placeholder="Jumlah Bayar" onkeyup="calculateChange()">
                            <div class="summary-row" style="font-size:18px;margin:15px 0;">
                                <span>Kembalian:</span>
                                <span id="change" style="color:#8B4513;font-weight:bold;">Rp 0</span>
                            </div>
                            <input type="text" id="note" placeholder="Catatan (opsional)">
                            <input type="hidden" id="items_input" name="items" value="">
                            <button class="btn-process" id="processBtn" onclick="processTransaction()" disabled>Proses Transaksi</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let cart = [];
        window.onload = () => searchProducts();

        function searchProducts() {
            const keyword = document.getElementById('searchInput').value;
            fetch(`penjualan.php?action=search_buku&keyword=${encodeURIComponent(keyword)}`)
                .then(r => r.json()).then(data => {
                    document.getElementById('productGrid').innerHTML = data.map(p => `
                        <div class="product-card" onclick="addToCart(${JSON.stringify(p).replace(/"/g, '&quot;')})">
                            <div style="display:flex;align-items:center;gap:10px;padding:1px;">
                                <img src="assets/produk/${p.kode_buku}.jpg" onerror="this.src='assets/img/default.jpg'" 
                                     style="width:50px;height:50px;border-radius:12px;object-fit:cover;box-shadow:0 4px 10px rgba(162,89,255,0.2);">
                                <div style="flex:1;">
                                    <div style="font-weight:700;color:#e0e0e0;">${p.nama_buku}</div>
                                    <div style="color:#A259FF;font-weight:bold;font-size:16px;">Rp ${p.harga_jual.toLocaleString()}</div>
                                    <div style="font-size:12px;color:#7a7a9e;">Stok: ${p.stok} ${p.satuan || 'pcs'}</div>
                                </div>
                            </div>
                        </div>
                    `).join('');
                });
        }

        function addToCart(product) {
            if (product.stok <= 0) {
                alert('Stok habis!');
                return;
            }
            
            const existing = cart.find(i => i.id === product.id);
            if (existing) {
                if (existing.quantity < product.stok) {
                    existing.quantity++;
                } else {
                    alert('Stok tidak cukup!');
                    return;
                }
            } else {
                cart.push({
                    id: product.id,
                    kode_buku: product.kode_buku,
                    nama_buku: product.nama_buku,
                    price: product.harga_jual,
                    diskon: product.diskon || 0,
                    stok: product.stok,
                    quantity: 1,
                    satuan: product.satuan || 'pcs'
                });
            }
            updateCartDisplay();
        }

        function updateCartDisplay() {
            const el = document.getElementById('cartItems');
            if (cart.length === 0) {
                el.innerHTML = `<div style="text-align:center;padding:60px 20px;color:#6b6b8a;">
                    <div style="font-size:80px;margin-bottom:20px;opacity:0.3;">🛒</div>
                    <p style="font-size:18px;font-weight:600;margin:0;color:#e0e0e0;">Keranjang Kosong</p>
                    <small style="color:#7a7a9e;">Pilih buku untuk memulai transaksi</small>
                </div>`;
                updateSummary();
                return;
            }

            el.innerHTML = cart.map((item, i) => {
                const subtotal = (item.price * item.quantity) - (item.diskon || 0);
                return `
                <div style="background:#1a1a2e;border:2px solid #2a2a3e;border-radius:16px;padding:16px;margin-bottom:14px;box-shadow:0 4px 15px rgba(162,89,255,0.08);">
                    <div style="display:flex;gap:14px;align-items:start;">
                        <div style="flex:1;">
                            <div style="font-weight:700;font-size:15px;color:#e0e0e0;margin-bottom:4px;">${item.nama_buku}</div>
                            <div style="font-size:13px;color:#7a7a9e;margin-bottom:8px;">
                                Rp ${item.price.toLocaleString()} × ${item.quantity}
                                ${item.diskon > 0 ? '<span style="background:#2a2a3e;color:#A259FF;padding:2px 8px;border-radius:8px;font-size:11px;margin-left:6px;">−Rp'+item.diskon.toLocaleString()+'</span>' : ''}
                            </div>
                            <div style="font-weight:bold;color:#A259FF;font-size:16px;">Rp ${Math.max(0, subtotal).toLocaleString()}</div>
                        </div>
                        <div style="display:flex;flex-direction:column;gap:8px;align-items:end;">
                            <div style="background:#0f0f1a;border:2px solid #A259FF;border-radius:12px;padding:4px;display:flex;align-items:center;gap:6px;">
                                <button onclick="updateQty(${i},-1)" style="width:28px;height:28px;background:#A259FF;color:white;border:none;border-radius:8px;font-size:16px;cursor:pointer;">−</button>
                                <input type="number" value="${item.quantity}" min="1" max="${item.stok}" style="width:40px;text-align:center;border:none;background:transparent;font-weight:bold;color:#A259FF;" onchange="setQty(${i},this.value)">
                                <button onclick="updateQty(${i},1)" style="width:28px;height:28px;background:#A259FF;color:white;border:none;border-radius:8px;font-size:16px;cursor:pointer;">+</button>
                            </div>
                            <button onclick="rm(${i})" style="background:#dc2626;color:white;border:none;border-radius:10px;padding:6px 12px;font-size:11px;cursor:pointer;">Hapus</button>
                        </div>
                    </div>
                </div>`;
            }).join('');
            updateSummary();
        }

        function updateQty(i, d) {
            const n = cart[i].quantity + d;
            if (n >= 1 && n <= cart[i].stok) {
                cart[i].quantity = n;
                updateCartDisplay();
            }
        }

        function setQty(i, v) {
            const n = parseInt(v);
            if (n >= 1 && n <= cart[i].stok) {
                cart[i].quantity = n;
                updateCartDisplay();
            }
        }

        function rm(i) {
            cart.splice(i, 1);
            updateCartDisplay();
        }

        function updateSummary() {
            // Hitung subtotal dari semua item di keranjang
            const subtotal = cart.reduce((s, i) => s + Math.max(0, (i.price * i.quantity) - (i.diskon || 0)), 0);
            
            // Ambil diskon transaksi (tambahan)
            const diskon_transaksi = parseFloat(document.getElementById('diskonInput').value) || 0;
            
            // Total setelah diskon
            const after_diskon = Math.max(0, subtotal - diskon_transaksi);
            
            // PPN (hitung dari total setelah diskon)
            const ppn_persen = <?php echo $ppn_persen; ?>;
            const ppn = after_diskon * (ppn_persen / 100);
            
            // Total akhir
            const total = after_diskon + ppn;
            
            // Update display
            document.getElementById('subtotal').textContent = 'Rp ' + subtotal.toLocaleString();
            document.getElementById('diskonDisplay').textContent = 'Rp ' + diskon_transaksi.toLocaleString();
            document.getElementById('ppn').textContent = 'Rp ' + ppn.toLocaleString();
            document.getElementById('total').textContent = 'Rp ' + total.toLocaleString();
            
            // Hitung kembalian
            calculateChange();
        }

        function calculateChange() {
            const total = parseFloat(document.getElementById('total').textContent.replace(/[^0-9]/g, '')) || 0;
            const bayar = parseFloat(document.getElementById('paymentInput').value) || 0;
            const kembali = Math.max(0, bayar - total);
            
            document.getElementById('change').textContent = 'Rp ' + kembali.toLocaleString();
            
            // Enable/disable tombol proses
            const btnProcess = document.getElementById('processBtn');
            btnProcess.disabled = cart.length === 0 || bayar < total;
        }

        function processTransaction() {
            if (cart.length === 0) {
                alert('Keranjang kosong!');
                return;
            }
            
            const total = parseFloat(document.getElementById('total').textContent.replace(/[^0-9]/g, '')) || 0;
            const bayar = parseFloat(document.getElementById('paymentInput').value) || 0;
            
            if (bayar < total) {
                alert('Jumlah bayar kurang!');
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'penjualan.php';
            
            const add = (n, v) => {
                const i = document.createElement('input');
                i.type = 'hidden';
                i.name = n;
                i.value = v;
                form.appendChild(i);
            };
            
            add('action', 'process_transaction');
            add('items', JSON.stringify(cart));
            add('customer_id', document.getElementById('customer_id').value);
            add('diskon', document.getElementById('diskonInput').value);
            add('ppn', parseFloat(document.getElementById('ppn').textContent.replace(/[^0-9]/g, '')) || 0);
            add('total_harga', total);
            add('total_bayar', bayar);
            add('kembalian', parseFloat(document.getElementById('change').textContent.replace(/[^0-9]/g, '')) || 0);
            add('note', document.getElementById('note').value);
            add('metode_pembayaran', document.getElementById('metode_pembayaran').value);
            
            document.body.appendChild(form);
            form.submit();
        }

        // Event listener untuk real-time update
        document.getElementById('diskonInput').addEventListener('keyup', updateSummary);
        document.getElementById('paymentInput').addEventListener('keyup', calculateChange);
        document.getElementById('metode_pembayaran').addEventListener('change', function() {
            // Metode pembayaran hanya update di database, tidak perlu re-calculate
            console.log('Metode pembayaran: ' + this.value);
        });
    </script>
</body>
</html>