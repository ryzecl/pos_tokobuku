<?php
require_once 'config/config.php';
requireRole(['admin', 'gudang']);

require_once 'models/Buku.php';
require_once 'models/KategoriBuku.php';

// ==================== KONFIGURASI REFRESH CACHE NODE.JS ====================
define('NODE_SERVER_URL', 'http://localhost:3000/refresh-buku-cache');
define('CACHE_REFRESH_SECRET', 'AkusukAC00kl4444T');

function refreshNodeCache()
{
    $ch = curl_init(NODE_SERVER_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Secret-Key: ' . CACHE_REFRESH_SECRET
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        error_log("Gagal refresh cache Node.js: HTTP $httpCode | Response: $response");
    } else {
        error_log("Cache Node.js berhasil di-refresh setelah perubahan buku.");
    }
}

// =============================================================================

$database = new Database();
$db = $database->getConnection();

$buku = new Buku($db);
$kategori = new KategoriBuku($db);

$message = '';
$message_type = '';

$uploadFolder = __DIR__ . "/assets/produk/";
if (!is_dir($uploadFolder)) {
    mkdir($uploadFolder, 0775, true);
}

/**
 * Upload foto & return nama file (tanpa timestamp)
 */
function uploadFotoCover($fieldName, $kodeBuku, $uploadFolder)
{
    if (empty($kodeBuku)) {
        error_log("Upload failed: Kode buku empty");
        return false;
    }

    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        $error = $_FILES[$fieldName]['error'] ?? 'Not set';
        error_log("Upload failed for $kodeBuku: File error code $error");
        return false;
    }

    $tmpFile = $_FILES[$fieldName]['tmp_name'];
    $fileSize = $_FILES[$fieldName]['size'];

    if ($fileSize > 5 * 1024 * 1024) {
        error_log("Upload failed for $kodeBuku: Size $fileSize exceeds limit");
        return false;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $tmpFile);
    finfo_close($finfo);

    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mimeType, $allowedMimeTypes)) {
        error_log("Upload failed for $kodeBuku: Mime type $mimeType not allowed");
        return false;
    }

    $safeKodeBase = preg_replace('/[^A-Za-z0-9_\-]/', '_', $kodeBuku);
    if (empty($safeKodeBase)) {
        error_log("Upload failed for $kodeBuku: sanitized kode empty");
        return false;
    }

    if (substr($uploadFolder, -1) !== DIRECTORY_SEPARATOR) $uploadFolder .= DIRECTORY_SEPARATOR;

    $targetFileName = $safeKodeBase . '.jpg';
    $targetFile = $uploadFolder . $targetFileName;

    if (file_exists($targetFile)) {
        unlink($targetFile);
        error_log("Old file deleted for $kodeBuku: $targetFile");
    }

    $gdAvailable = function_exists('imagecreatefromjpeg') && function_exists('imagejpeg');

    if ($gdAvailable) {
        $image = null;
        switch ($mimeType) {
            case 'image/jpeg': $image = @imagecreatefromjpeg($tmpFile); break;
            case 'image/png': $image = @imagecreatefrompng($tmpFile); break;
            case 'image/gif': $image = @imagecreatefromgif($tmpFile); break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) $image = @imagecreatefromwebp($tmpFile);
                break;
        }

        if ($image) {
            $success = @imagejpeg($image, $targetFile, 85);
            imagedestroy($image);
            if ($success) {
                error_log("Upload success for $kodeBuku: Saved to $targetFile");
                return $targetFileName;
            }
        }
    }

    // Fallback: preserve original extension
    $origExt = pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION);
    $origExt = strtolower(preg_replace('/[^a-z0-9]+/', '', $origExt));
    if (!in_array($origExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) $origExt = 'jpg';

    $fallbackName = $safeKodeBase . '.' . $origExt;
    $fallbackPath = $uploadFolder . $fallbackName;

    if (file_exists($fallbackPath)) unlink($fallbackPath);

    if (is_uploaded_file($tmpFile) && move_uploaded_file($tmpFile, $fallbackPath)) {
        error_log("Upload fallback success for $kodeBuku: moved to $fallbackPath");
        return $fallbackName;
    }

    return false;
}

function deleteFotoCover($kodeBuku, $uploadFolder)
{
    if (empty($kodeBuku)) return false;

    $safeKodeBase = preg_replace('/[^A-Za-z0-9_\-]/', '_', $kodeBuku);
    if (empty($safeKodeBase)) return false;

    if (substr($uploadFolder, -1) !== DIRECTORY_SEPARATOR) $uploadFolder .= DIRECTORY_SEPARATOR;

    $possibleFiles = [
        $uploadFolder . $safeKodeBase . '.jpg',
        $uploadFolder . $safeKodeBase . '.png',
        $uploadFolder . $safeKodeBase . '.gif',
        $uploadFolder . $safeKodeBase . '.webp'
    ];

    foreach ($possibleFiles as $filePath) {
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                error_log("Foto cover deleted: $filePath");
            }
        }
    }
}

// Handle POST
if ($_POST && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'create':
                $buku->kode_buku = sanitizeInput($_POST['kode_buku']);
                $buku->nama_buku = sanitizeInput($_POST['nama_buku']);
                $buku->kategori_id = sanitizeInput($_POST['kategori_id']);
                $buku->satuan = sanitizeInput($_POST['satuan']);
                $buku->harga_beli = sanitizeInput($_POST['harga_beli']);
                $buku->harga_jual = sanitizeInput($_POST['harga_jual']);
                $buku->diskon = sanitizeInput($_POST['diskon'] ?? 0);
                $buku->stok = sanitizeInput($_POST['stok']);
                $buku->stok_minimum = sanitizeInput($_POST['stok_minimum']);
                $buku->tanggal_expired = !empty($_POST['tanggal_expired']) ? sanitizeInput($_POST['tanggal_expired']) : null;
                $buku->deskripsi = sanitizeInput($_POST['deskripsi']);

                $fileCover = uploadFotoCover("foto_cover", $buku->kode_buku, $uploadFolder);
                if ($fileCover) $buku->foto_cover = $fileCover;

                if ($buku->create()) {
                    $message = 'Data buku berhasil ditambahkan!';
                    $message_type = 'success';
                    refreshNodeCache();
                } else {
                    $message = 'Gagal menambahkan data buku! Cek kode buku apakah sudah ada.';
                    $message_type = 'error';
                    if ($fileCover) deleteFotoCover($buku->kode_buku, $uploadFolder);
                }
                break;

            case 'update':
                $oldKodeBuku = null;
                $buku->id = sanitizeInput($_POST['id']);
                if ($buku->readOne()) $oldKodeBuku = $buku->kode_buku;

                $buku->kode_buku = sanitizeInput($_POST['kode_buku']);
                $buku->nama_buku = sanitizeInput($_POST['nama_buku']);
                $buku->kategori_id = sanitizeInput($_POST['kategori_id']);
                $buku->satuan = sanitizeInput($_POST['satuan']);
                $buku->harga_beli = sanitizeInput($_POST['harga_beli']);
                $buku->harga_jual = sanitizeInput($_POST['harga_jual']);
                $buku->diskon = sanitizeInput($_POST['diskon'] ?? 0);
                $buku->stok = sanitizeInput($_POST['stok']);
                $buku->stok_minimum = sanitizeInput($_POST['stok_minimum']);
                $buku->tanggal_expired = !empty($_POST['tanggal_expired']) ? sanitizeInput($_POST['tanggal_expired']) : null;
                $buku->deskripsi = sanitizeInput($_POST['deskripsi']);

                $newFileCover = null;
                if (!empty($_FILES['foto_cover']['name'])) {
                    $newFileCover = uploadFotoCover("foto_cover", $buku->kode_buku, $uploadFolder);
                    if ($newFileCover) $buku->foto_cover = $newFileCover;
                }

                if ($buku->update()) {
                    $message = 'Data buku berhasil diperbarui!';
                    $message_type = 'success';
                    refreshNodeCache();
                    if ($oldKodeBuku && $oldKodeBuku !== $buku->kode_buku) {
                        deleteFotoCover($oldKodeBuku, $uploadFolder);
                    }
                } else {
                    $message = 'Gagal memperbarui data buku!';
                    $message_type = 'error';
                    if ($newFileCover) deleteFotoCover($buku->kode_buku, $uploadFolder);
                }
                break;

            case 'delete':
                $buku->id = sanitizeInput($_POST['id']);
                if ($buku->readOne()) {
                    deleteFotoCover($buku->kode_buku, $uploadFolder);
                }
                if ($buku->delete()) {
                    $message = 'Data buku & foto berhasil dihapus!';
                    $message_type = 'success';
                    refreshNodeCache();
                } else {
                    $message = 'Gagal menghapus data buku!';
                    $message_type = 'error';
                }
                break;
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'error';
    }
}

$stmt = $buku->readAll();
$kategori_stmt = $kategori->readAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Buku - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dynamic.php">
</head>
<body>
    <div class="main-container">
        <?php $role = $_SESSION['user_role']; require_once 'components/sidebar.php'; ?>

        <main class="main-content">
            <header class="top-nav">
                <h1>Data Buku</h1>
                <div class="user-info">
                    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)); ?></div>
                    <div class="user-details">
                        <div class="user-name"><?php echo $_SESSION['nama_lengkap']; ?></div>
                        <div class="user-role"><?php echo ucfirst($_SESSION['user_role']); ?></div>
                    </div>
                </div>
            </header>

            <div class="content">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- FORM TAMBAH BUKU BARU -->
                <div class="form-container">
                    <h2>Tambah Buku Baru</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="create">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="kode_buku">Kode Buku <span style="color: red;">*</span></label>
                                <input type="text" id="kode_buku" name="kode_buku" required placeholder="cth: B001">
                            </div>
                            <div class="form-group">
                                <label for="nama_buku">Nama Buku <span style="color: red;">*</span></label>
                                <input type="text" id="nama_buku" name="nama_buku" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="kategori_id">Kategori <span style="color: red;">*</span></label>
                                <select id="kategori_id" name="kategori_id" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php
                                    $kategori_stmt_temp = $kategori->readAll();
                                    if ($kategori_stmt_temp) {
                                        while ($row = $kategori_stmt_temp->fetch(PDO::FETCH_ASSOC)): ?>
                                            <option value="<?php echo $row['id']; ?>">
                                                <?php echo htmlspecialchars($row['nama_kategori']); ?>
                                            </option>
                                        <?php endwhile;
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="satuan">Satuan <span style="color: red;">*</span></label>
                                <input type="text" id="satuan" name="satuan" required placeholder="cth: Pcs, Lusin">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="harga_beli">Harga Beli <span style="color: red;">*</span></label>
                                <input type="number" id="harga_beli" name="harga_beli" required min="0" step="100">
                            </div>
                            <div class="form-group">
                                <label for="harga_jual">Harga Jual <span style="color: red;">*</span></label>
                                <input type="number" id="harga_jual" name="harga_jual" required min="0" step="100">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="diskon">Diskon Default (Rp)</label>
                                <input type="number" id="diskon" name="diskon" min="0" step="100" value="0">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="stok">Stok <span style="color: red;">*</span></label>
                                <input type="number" id="stok" name="stok" required min="0">
                            </div>
                            <div class="form-group">
                                <label for="stok_minimum">Stok Minimum <span style="color: red;">*</span></label>
                                <input type="number" id="stok_minimum" name="stok_minimum" required min="0">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="foto_cover">Foto Cover Buku</label>
                                <input type="file" id="foto_cover" name="foto_cover" accept="image/*">
                                <div style="margin-top: 12px;">
                                    <img id="foto_preview" src="" alt="Preview Cover" 
                                         style="max-height: 180px; border: 2px solid #ccc; border-radius: 10px; display: none; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="tanggal_expired">Tanggal Terbit</label>
                                <input type="date" id="tanggal_expired" name="tanggal_expired">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea id="deskripsi" name="deskripsi" rows="4"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Tambah Buku</button>
                    </form>
                </div>

                <!-- TABEL DAFTAR BUKU -->
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Daftar Buku (Total: <?php echo $buku->getTotalBuku(); ?>)</h3>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Buku</th>
                                <th>Kategori</th>
                                <th>Satuan</th>
                                <th>Harga Beli</th>
                                <th>Harga Jual</th>
                                <th>Diskon</th>
                                <th>Stok</th>
                                <th>Terbit</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($stmt): while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['kode_buku']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['nama_buku']); ?></td>
                                    <td><?php echo $row['nama_kategori'] ?? '-'; ?></td>
                                    <td><?php echo htmlspecialchars($row['satuan']); ?></td>
                                    <td><?php echo formatCurrency($row['harga_beli']); ?></td>
                                    <td><?php echo formatCurrency($row['harga_jual']); ?></td>
                                    <td><?php echo formatCurrency($row['diskon'] ?? 0); ?></td>
                                    <td>
                                        <span class="badge <?php echo $row['stok'] <= $row['stok_minimum'] ? 'badge-danger' : 'badge-success'; ?>">
                                            <?php echo $row['stok']; ?> / <?php echo $row['stok_minimum']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $row['tanggal_expired'] ? date('d/m/Y', strtotime($row['tanggal_expired'])) : '-'; ?></td>
                                    <td>
                                        <button onclick="editbuku(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES); ?>)" class="btn btn-warning btn-sm">Edit</button>
                                        <button onclick="deletebuku(<?php echo $row['id']; ?>)" class="btn btn-danger btn-sm">Hapus</button>
                                    </td>
                                </tr>
                            <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- EDIT MODAL (DARK MODE) -->
    <div id="editModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); z-index:1000; backdrop-filter:blur(4px);">
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:#1a1a1a; color:#e0e0e0; padding:30px; border-radius:12px; width:90%; max-width:620px; max-height:90vh; overflow-y:auto; box-shadow:0 10px 30px rgba(0,0,0,0.7);">
            <h2 style="margin-top:0; color:#fff; font-size:1.6em;">Edit Buku</h2>
            <form method="POST" id="editForm" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">

                <div class="form-row">
                    <div class="form-group">
                        <label style="color:#ccc;">Kode Buku</label>
                        <input type="text" id="edit_kode_buku" name="kode_buku" required style="width:100%; padding:10px; background:#2d2d2d; border:1px solid #444; color:#fff; border-radius:6px;">
                    </div>
                    <div class="form-group">
                        <label style="color:#ccc;">Nama Buku</label>
                        <input type="text" id="edit_nama_buku" name="nama_buku" required style="width:100%; padding:10px; background:#2d2d2d; border:1px solid #444; color:#fff; border-radius:6px;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label style="color:#ccc;">Kategori</label>
                        <select id="edit_kategori_id" name="kategori_id" required style="width:100%; padding:10px; background:#2d2d2d; border:1px solid #444; color:#fff; border-radius:6px;"></select>
                    </div>
                    <div class="form-group">
                        <label style="color:#ccc;">Satuan</label>
                        <input type="text" id="edit_satuan" name="satuan" required style="width:100%; padding:10px; background:#2d2d2d; border:1px solid #444; color:#fff; border-radius:6px;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label style="color:#ccc;">Harga Beli</label>
                        <input type="number" id="edit_harga_beli" name="harga_beli" required min="0" step="100" style="width:100%; padding:10px; background:#2d2d2d; border:1px solid #444; color:#fff; border-radius:6px;">
                    </div>
                    <div class="form-group">
                        <label style="color:#ccc;">Harga Jual</label>
                        <input type="number" id="edit_harga_jual" name="harga_jual" required min="0" step="100" style="width:100%; padding:10px; background:#2d2d2d; border:1px solid #444; color:#fff; border-radius:6px;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label style="color:#ccc;">Diskon (Rp)</label>
                        <input type="number" id="edit_diskon" name="diskon" min="0" step="100" value="0" style="width:100%; padding:10px; background:#2d2d2d; border:1px solid #444; color:#fff; border-radius:6px;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label style="color:#ccc;">Stok</label>
                        <input type="number" id="edit_stok" name="stok" required min="0" style="width:100%; padding:10px; background:#2d2d2d; border:1px solid #444; color:#fff; border-radius:6px;">
                    </div>
                    <div class="form-group">
                        <label style="color:#ccc;">Stok Minimum</label>
                        <input type="number" id="edit_stok_minimum" name="stok_minimum" required min="0" style="width:100%; padding:10px; background:#2d2d2d; border:1px solid #444; color:#fff; border-radius:6px;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label style="color:#ccc;">Tanggal Terbit</label>
                        <input type="date" id="edit_tanggal_expired" name="tanggal_expired" style="width:100%; padding:10px; background:#2d2d2d; border:1px solid #444; color:#fff; border-radius:6px;">
                    </div>
                    <div class="form-group">
                        <label style="color:#ccc;">Foto Cover (kosongkan jika tidak diubah)</label>
                        <input type="file" id="edit_foto_cover" name="foto_cover" accept="image/*" style="width:100%; padding:8px; background:#2d2d2d; border:1px solid #444; color:#fff; border-radius:6px;">
                        <div style="margin-top:12px;">
                            <img id="edit_foto_preview" src="" alt="Preview" style="max-height:140px; border:2px solid #444; border-radius:8px; display:none;">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label style="color:#ccc;">Deskripsi</label>
                    <textarea id="edit_deskripsi" name="deskripsi" rows="4" style="width:100%; padding:10px; background:#2d2d2d; border:1px solid #444; color:#fff; border-radius:6px; resize:vertical;"></textarea>
                </div>

                <div style="display:flex; gap:12px; margin-top:25px; justify-content:flex-end;">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // PREVIEW FOTO DI FORM TAMBAH BUKU (PASTI MUNCUL SEKARANG)
        document.addEventListener('DOMContentLoaded', function() {
            const inputFoto = document.getElementById('foto_cover');
            const preview = document.getElementById('foto_preview');

            if (inputFoto && preview) {
                inputFoto.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(ev) {
                            preview.src = ev.target.result;
                            preview.style.display = 'block';
                        }
                        reader.readAsDataURL(file);
                    } else {
                        preview.src = '';
                        preview.style.display = 'none';
                    }
                });
            }
        });

        // Load kategori untuk modal edit
        function loadKategori() {
            const select = document.getElementById('edit_kategori_id');
            select.innerHTML = '<option value="">-- Pilih Kategori --</option>';
            <?php
            $kategori_stmt = $kategori->readAll();
            if ($kategori_stmt) {
                while ($row = $kategori_stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "const opt = document.createElement('option');";
                    echo "opt.value = '{$row['id']}';";
                    echo "opt.text = '" . htmlspecialchars($row['nama_kategori'], ENT_QUOTES) . "';";
                    echo "select.appendChild(opt);";
                }
            }
            ?>
        }

        function editbuku(data) {
            loadKategori();
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_kode_buku').value = data.kode_buku;
            document.getElementById('edit_nama_buku').value = data.nama_buku;
            document.getElementById('edit_kategori_id').value = data.kategori_id || '';
            document.getElementById('edit_satuan').value = data.satuan;
            document.getElementById('edit_harga_beli').value = data.harga_beli;
            document.getElementById('edit_harga_jual').value = data.harga_jual;
            document.getElementById('edit_diskon').value = data.diskon || 0;
            document.getElementById('edit_stok').value = data.stok;
            document.getElementById('edit_stok_minimum').value = data.stok_minimum;
            document.getElementById('edit_tanggal_expired').value = data.tanggal_expired || '';
            document.getElementById('edit_deskripsi').value = data.deskripsi || '';

            const preview = document.getElementById('edit_foto_preview');
            if (data.foto_cover) {
                preview.src = 'assets/produk/' + data.foto_cover + '?t=' + new Date().getTime();
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }

            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function deletebuku(id) {
            if (confirm('Apakah Anda yakin ingin menghapus buku ini? Data tidak bisa dikembalikan!')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="${id}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Tutup modal saat klik luar
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });
    </script>
</body>
</html>