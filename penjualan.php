<?php
// penjualan.php — Full backend + UI (copy-paste siap pakai)
// Backend: robust, aman; UI: sesuai style yang lo minta

require_once 'config/config.php';
requireRole(['admin', 'kasir']);

require_once 'models/Buku.php';
require_once 'models/Penjualan.php';
require_once 'models/Customer.php';
require_once 'models/Pengaturan.php';

$database = new Database();
$db = $database->getConnection();

$buku = new buku($db);
$penjualan = new Penjualan($db);
$customer = new Customer($db);
$pengaturan = new Pengaturan($db);

$ppn_persen = floatval($pengaturan->get('ppn_persen') ?? 10);

$message = '';
$message_type = '';

// Pastikan $role untuk sidebar.php
$role = $_SESSION['user_role'] ?? 'kasir';

// ---- AJAX handlers (search & get) ----
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    switch ($_GET['action']) {
        case 'search_buku':
            $keyword = sanitizeInput($_GET['keyword'] ?? '');
            $stmt = $buku->search($keyword);
            $results = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Pastikan struktur yang frontend butuhkan
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
            echo json_encode($results);
            exit;

        case 'get_buku':
            $buku_id = sanitizeInput($_GET['buku_id']);
            $buku->id = $buku_id;
            if ($buku->readOne()) {
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
                echo json_encode(['error' => 'buku tidak ditemukan']);
            }
            exit;
    }
}

// ---- PROCESS TRANSACTION (POST) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process_transaction') {
    try {
        $db->beginTransaction();

        // Set header penjualan (sanitize)
        $penjualan->no_transaksi = $penjualan->generateNoTransaksi();
        $penjualan->user_id = $_SESSION['user_id'] ?? null;
        $penjualan->customer_id = !empty($_POST['customer_id']) ? sanitizeInput($_POST['customer_id']) : null;
        $penjualan->diskon = isset($_POST['diskon']) ? floatval($_POST['diskon']) : 0;
        $penjualan->ppn = isset($_POST['ppn']) ? floatval($_POST['ppn']) : 0;
        $penjualan->total_harga = isset($_POST['total_harga']) ? floatval($_POST['total_harga']) : 0;
        $penjualan->total_bayar = isset($_POST['total_bayar']) ? floatval($_POST['total_bayar']) : 0;
        $penjualan->kembalian = isset($_POST['kembalian']) ? floatval($_POST['kembalian']) : 0;
        $penjualan->note = !empty($_POST['note']) ? sanitizeInput($_POST['note']) : null;
        $penjualan->metode_pembayaran = $_POST['metode_pembayaran'];

        // Ambil items, decode dengan toleransi terhadap escaping/html entities
        $itemsRaw = $_POST['items'] ?? '';
        $items = json_decode($itemsRaw, true);
        if ($items === null) {
            // coba unescape & stripslashes lalu decode ulang
            $try = html_entity_decode(stripslashes($itemsRaw));
            $items = json_decode($try, true);
        }
        if (!is_array($items) || count($items) === 0) {
            throw new Exception('Detail transaksi kosong atau tidak valid.');
        }

        // Validasi item dan persiapkan detail array
        $details = [];
        foreach ($items as $idx => $it) {
            $buku_id = isset($it['id']) ? intval($it['id']) : (isset($it['buku_id']) ? intval($it['buku_id']) : 0);
            $quantity = isset($it['quantity']) ? intval($it['quantity']) : (isset($it['qty']) ? intval($it['qty']) : 0);
            $price = isset($it['price']) ? floatval($it['price']) : (isset($it['harga']) ? floatval($it['harga']) : 0);
            $subtotal = isset($it['subtotal']) ? floatval($it['subtotal']) : max(0, $price * $quantity);

            if ($buku_id <= 0 || $quantity <= 0) {
                throw new Exception("Item ke-" . ($idx + 1) . " tidak valid (id atau quantity).");
            }

            // Cek stok saat ini via model buku
            $buku->id = $buku_id;
            if (!$buku->readOne()) {
                throw new Exception("buku dengan ID {$buku_id} tidak ditemukan.");
            }
            if ($buku->stok < $quantity) {
                throw new Exception("Stok untuk '{$buku->nama_buku}' tidak cukup. (tersisa: {$buku->stok})");
            }

            $details[] = [
                'buku_id' => $buku_id,
                'jumlah' => $quantity,
                'harga_satuan' => $price,
                'subtotal' => $subtotal
            ];
        }

        // Insert header penjualan
        if (!$penjualan->create()) {
            throw new Exception('Gagal membuat transaksi (header).');
        }

        // Dapatkan ID penjualan yang baru
        $penjualan_id = $penjualan->id ?? $db->lastInsertId();
        if (!$penjualan_id) {
            throw new Exception('Tidak mendapatkan ID penjualan.');
        }

        // Insert detail_penjualan (kolom: penjualan_id, buku_id, jumlah, harga_satuan, subtotal)
        $detail_query = "INSERT INTO detail_penjualan (penjualan_id, buku_id, jumlah, harga_satuan, subtotal) 
                         VALUES (:penjualan_id, :buku_id, :jumlah, :harga_satuan, :subtotal)";
        $detail_stmt = $db->prepare($detail_query);

        foreach ($details as $d) {
            $detail_stmt->bindValue(':penjualan_id', $penjualan_id, PDO::PARAM_INT);
            $detail_stmt->bindValue(':buku_id', $d['buku_id'], PDO::PARAM_INT);
            $detail_stmt->bindValue(':jumlah', $d['jumlah'], PDO::PARAM_INT);
            $detail_stmt->bindValue(':harga_satuan', $d['harga_satuan']);
            $detail_stmt->bindValue(':subtotal', $d['subtotal']);
            if (!$detail_stmt->execute()) {
                throw new Exception('Gagal menyimpan detail penjualan.');
            }

            // update stok (kurangi)
            if (!$buku->updateStok($d['buku_id'], -$d['jumlah'])) {
                throw new Exception('Gagal memperbarui stok untuk buku ID ' . $d['buku_id']);
            }
        }

        $db->commit();
        header('Location: struk.php?id=' . $penjualan_id);
        exit();
    } catch (Exception $e) {
        $db->rollBack();
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Ambil data customer
$customer_stmt = $customer->readAll();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjualan - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dynamic.php">
    <style>
        .pos-container {
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 20px;
            height: calc(100vh - 120px);
        }

        .product-section,
        .cart-section {
            background: white;
            border-radius: 16px;
            box-shadow: 0 0 8px 25px rgba(139, 69, 19, 0.1);
            padding: 24px;
        }

        .search-box input {
            width: 100%;
            padding: 14px;
            border: 2px solid #e6d6c2;
            border-radius: 12px;
            font-size: 16px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 16px;
        }

        .product-card {
            border: 2px solid #f0e6d6;
            border-radius: 14px;
            padding: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fff8f0;
        }

        .product-card:hover {
            border-color: #D2691E;
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(139, 69, 19, 0.15);
        }

        .product-name {
            font-weight: 700;
            color: #2c1b18;
            margin-bottom: 6px;
        }

        .product-price {
            color: #8B4513;
            font-weight: bold;
            font-size: 18px;
        }

        .product-stock {
            font-size: 13px;
            color: #A0522D;
        }

        .cart-items {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 20px;
            padding-right: 8px;
        }

        .cart-items::-webkit-scrollbar {
            width: 8px;
        }

        .cart-items::-webkit-scrollbar-track {
            background: #f0e6d6;
            border-radius: 10px;
        }

        .cart-items::-webkit-scrollbar-thumb {
            background: #D2691E;
            border-radius: 10px;
        }

        .cart-summary {
            border-top: 3px solid #D2691E;
            padding-top: 18px;
            background: #fff8f0;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 16px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 15px;
        }

        .summary-row.total {
            font-weight: bold;
            font-size: 20px;
            color: #8B4513;
            padding-top: 12px;
            border-top: 1px dashed #D2691E;
        }

        .payment-section input,
        .payment-section select {
            width: 100%;
            padding: 14px;
            border: 2px solid #e6d6c2;
            border-radius: 12px;
            margin-bottom: 12px;
            font-size: 15px;
        }

        .btn-process {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #8B4513, #D2691E);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 6px 20px rgba(139, 69, 19, 0.3);
        }

        .btn-process:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(139, 69, 19, 0.4);
        }

        .btn-process:disabled {
            background: #bbb;
            cursor: not-allowed;
            transform: none;
        }
    </style>
</head>

<body>
    <div class="main-container">
        <!-- Sidebar -->
        <?php require_once 'sidebar.php'; ?>

        <main class="main-content">
            <header class="top-nav">
                <h1>Point of Sale (POS)</h1>
                <div class="user-info">
                    <div class="user-avatar" style="background:#D2691E;">
                        <?php echo strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?php echo $_SESSION['nama_lengkap']; ?></div>
                        <div class="user-role"><?php echo ucfirst($_SESSION['user_role']); ?></div>
                    </div>
                </div>
            </header>

            <div class="content">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
                <?php endif; ?>

                <div class="pos-container">
                    <div class="product-section">
                        <div class="search-box">
                            <input type="text" id="searchInput" placeholder="Cari buku..." onkeyup="searchProducts()">
                        </div>
                        <div id="productGrid" class="product-grid"></div>
                    </div>

                    <div class="cart-section">
                        <h3 style="margin:0 0 20px 0; color:#8B4513; font-size:22px;">Keranjang Belanja</h3>

                        <div class="cart-items" id="cartItems">
                            <div style="text-align:center;padding:60px 20px;color:#A0522D;">
                                <div style="font-size:80px;margin-bottom:20px;opacity:0.3;">Keranjang</div>
                                <p style="font-size:18px;font-weight:600;margin:0;">Keranjang Kosong</p>
                                <small>Pilih buku untuk memulai transaksi</small>
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
                            <select name="metode_pembayaran" id="metode_pembayaran" class="form-control" required>
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
                            <!-- Hidden input to safely keep items before building form -->
                            <input type="hidden" id="items_input" name="items" value="">
                            <button class="btn-process" id="processBtn" onclick="processTransaction()" disabled>
                                Proses Transaksi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let cart = [];
        let products = [];

        window.onload = () => searchProducts();

        function searchProducts() {
            const keyword = document.getElementById('searchInput').value;
            fetch(`penjualan.php?action=search_buku&keyword=${encodeURIComponent(keyword)}`)
                .then(r => r.json())
                .then(data => {
                    products = data;
                    displayProducts(data);
                });
        }

        function displayProducts(products) {
            const grid = document.getElementById('productGrid');
            grid.innerHTML = '';
            products.forEach(p => {
                const card = document.createElement('div');
                card.className = 'product-card';
                card.onclick = () => addToCart(p);

                card.innerHTML = `
            <div style="display:flex; align-items:center; gap:10px; padding:1px;">
                <img src="assets/produk/${p.kode_buku}.jpg" 
                     onerror="this.src='assets/produk/default.jpg'" 
                     style="width:50px; height:50px; border-radius:12px; object-fit:cover; box-shadow:0 4px 10px rgba(139,69,19,0.2);">
                <div style="flex:1;">
                    <div style="font-weight:700; color:#2c1b18;">${p.nama_buku}</div>
                    <div style="color:#8B4513; font-weight:bold; font-size:16px;">${formatCurrency(p.harga_jual)}</div>
                    <div style="font-size:12px; color:#A0522D;">Stok: ${p.stok} ${p.satuan || 'pcs'}</div>
                </div>
            </div>
        `;
                grid.appendChild(card);
            });
        }

        function addToCart(product) {
            if (product.stok <= 0) return alert('Stok habis!');
            const existing = cart.find(i => i.id === product.id);
            if (existing) {
                if (existing.quantity < product.stok) existing.quantity++;
                else return alert('Stok tidak cukup!');
            } else {
                cart.push({
                    id: product.id,
                    kode_buku: product.kode_buku,
                    nama_buku: product.nama_buku,
                    price: parseFloat(product.harga_jual),
                    diskon: parseFloat(product.diskon || 0),
                    quantity: 1,
                    max_stock: product.stok
                });
            }
            updateCartDisplay();
        }

        function updateCartDisplay() {
            const el = document.getElementById('cartItems');
            if (cart.length === 0) {
                el.innerHTML = `<div style="text-align:center;padding:60px 20px;color:#A0522D;">
                    <div style="font-size:80px;margin-bottom:20px;opacity:0.3;">Keranjang</div>
                    <p style="font-size:18px;font-weight:600;margin:0;">Keranjang Kosong</p>
                    <small>Pilih buku untuk memulai transaksi</small>
                </div>`;
                updateSummary();
                return;
            }

            el.innerHTML = '';
            cart.forEach((item, i) => {
                const subtotal = (item.price * item.quantity) - (item.diskon || 0);
                const before = item.price * item.quantity;

                const div = document.createElement('div');
                div.style.cssText = 'background:white;border:2px solid #f0e6d6;border-radius:16px;padding:16px;margin-bottom:14px;box-shadow:0 4px 15px rgba(139,69,19,0.08);';
                div.innerHTML = `
                    <div style="display:flex;gap:14px;align-items:start;">
                        <div style="flex:1;">
                            <div style="font-weight:700;font-size:15px;color:#2c1b18;margin-bottom:4px;">${item.nama_buku}</div>
                            <div style="font-size:13px;color:#A0522D;margin-bottom:8px;">
                                ${formatCurrency(item.price)} × ${item.quantity}<br>
                                ${item.diskon > 0 ? '<span style="background:#ffe6e6;color:#c0392b;padding:2px 8px;border-radius:8px;font-size:11px;margin-left:6px;">−'+formatCurrency(item.diskon)+'</span>' : ''}
                            </div>
                            ${item.diskon > 0 ? '<div style="font-size:11px;color:#999;text-decoration:line-through;margin-bottom:4px;">'+formatCurrency(before)+'</div>' : ''}
                            <div style="font-weight:bold;color:#8B4513;font-size:16px;">${formatCurrency(Math.max(0,subtotal))}</div>
                        </div>
                        <div style="display:flex;flex-direction:column;gap:8px;align-items:end;">
                            <div style="background:#fff8f0;border:2px solid #D2691E;border-radius:12px;padding:4px;display:flex;align-items:center;gap:6px;">
                                <button onclick="updateQuantity(${i},-1)" style="width:28px;height:28px;background:#D2691E;color:white;border:none;border-radius:8px;font-size:16px;cursor:pointer;">−</button>
                                <input type="number" value="${item.quantity}" min="1" max="${item.max_stock}"
                                       style="width:40px;text-align:center;border:none;background:transparent;font-weight:bold;color:#8B4513;"
                                       onchange="setQuantity(${i},this.value)">
                                <button onclick="updateQuantity(${i},1)" style="width:28px;height:28px;background:#D2691E;color:white;border:none;border-radius:8px;font-size:16px;cursor:pointer;">+</button>
                            </div>
                            <div style="background:#fff8f0;border:1px solid #e6d6c2;border-radius:10px;padding:4px 8px;width:100%;">
                                <input type="number" value="${item.diskon||0}" placeholder="Diskon"
                                       style="width:100%;border:none;background:transparent;text-align:right;font-size:12px;color:#8B4513;"
                                       onchange="updateItemDiskon(${i},this.value)" onkeyup="updateItemDiskon(${i},this.value)">
                            </div>
                            <button onclick="removeFromCart(${i})" style="background:#c0392b;color:white;border:none;border-radius:10px;padding:6px 12px;font-size:11px;cursor:pointer;">
                                Delete Hapus
                            </button>
                        </div>
                    </div>
                `;
                el.appendChild(div);
            });
            updateSummary();
        }

        function updateQuantity(i, c) {
            const it = cart[i];
            const n = it.quantity + c;
            if (n >= 1 && n <= it.max_stock) {
                it.quantity = n;
                updateCartDisplay();
            }
        }

        function setQuantity(i, v) {
            const n = parseInt(v);
            if (n >= 1 && n <= cart[i].max_stock) {
                cart[i].quantity = n;
                updateCartDisplay();
            }
        }

        function removeFromCart(i) {
            cart.splice(i, 1);
            updateCartDisplay();
        }

        function updateItemDiskon(i, v) {
            cart[i].diskon = parseFloat(v) || 0;
            updateCartDisplay();
        }

        function updateSummary() {
            const subtotal = cart.reduce((s, it) => s + Math.max(0, (it.price * it.quantity) - (it.diskon || 0)), 0);
            const diskon = parseFloat(document.getElementById('diskonInput').value) || 0;
            const afterDiskon = Math.max(0, subtotal - diskon);
            const ppn = afterDiskon * (<?php echo $ppn_persen; ?> / 100);
            const total = afterDiskon + ppn;

            document.getElementById('subtotal').textContent = formatCurrency(subtotal);
            document.getElementById('diskonDisplay').textContent = formatCurrency(diskon);
            document.getElementById('ppn').textContent = formatCurrency(ppn);
            document.getElementById('total').textContent = formatCurrency(total);
            calculateChange();
        }

        function calculateChange() {
            const subtotal = cart.reduce((s, it) => s + Math.max(0, (it.price * it.quantity) - (it.diskon || 0)), 0);
            const diskon = parseFloat(document.getElementById('diskonInput').value) || 0;
            const afterDiskon = Math.max(0, subtotal - diskon);
            const total = afterDiskon * (1 + <?php echo $ppn_persen; ?> / 100);
            const bayar = parseFloat(document.getElementById('paymentInput').value) || 0;
            const kembalian = bayar - total;

            document.getElementById('change').textContent = formatCurrency(Math.max(0, kembalian));
            document.getElementById('processBtn').disabled = cart.length === 0 || bayar < total;
        }

        function processTransaction() {
            const subtotal = cart.reduce((s, it) => s + Math.max(0, (it.price * it.quantity) - (it.diskon || 0)), 0);
            const diskon = parseFloat(document.getElementById('diskonInput').value) || 0;
            const afterDiskon = Math.max(0, subtotal - diskon);
            const ppn = afterDiskon * (<?php echo $ppn_persen; ?> / 100);
            const total = afterDiskon + ppn;
            const bayar = parseFloat(document.getElementById('paymentInput').value);

            if (bayar < total) return alert('Jumlah bayar kurang!');

            // set subtotal masing-masing item
            cart.forEach(it => it.subtotal = Math.max(0, (it.price * it.quantity) - (it.diskon || 0)));

            // set hidden input safely (hindari masalah escaping)
            document.getElementById('items_input').value = JSON.stringify(cart);

            // Build form via DOM (lebih aman ketimbang innerHTML untuk JSON)
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'penjualan.php';

            const hidden = (name, value) => {
                const i = document.createElement('input');
                i.type = 'hidden';
                i.name = name;
                i.value = value;
                form.appendChild(i);
            };

            hidden('action', 'process_transaction');
            hidden('items', document.getElementById('items_input').value);
            hidden('customer_id', document.getElementById('customer_id').value);
            hidden('diskon', diskon);
            hidden('ppn', ppn);
            hidden('total_harga', total);
            hidden('total_bayar', bayar);
            hidden('kembalian', bayar - total);
            hidden('note', document.getElementById('note').value);

            document.body.appendChild(form);
            form.submit();
        }

        function formatCurrency(n) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));
        }
    </script>
</body>

</html>