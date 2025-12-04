<?php
// Deteksi halaman saat ini
$current_page = basename($_SERVER['PHP_SELF']);
?>

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>

<body>
    <nav class="sidebar">
        <div class="sidebar-header">
            <img src="assets/DaebookLogo.svg" alt="Daebook Logo" style="height: 40px; margin-right: 10px;">
            <h2><?php echo htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></h2>
        </div>

        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link <?php echo ($current_page === 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i> Dashboard
                </a>
            </li>

            <?php if ($role === 'admin' || $role === 'kasir'): ?>
                <li class="nav-item">
                    <a href="penjualan.php" class="nav-link <?php echo ($current_page === 'penjualan.php') ? 'active' : ''; ?>">
                        <i class="bi bi-cart2"></i></i> Penjualan
                    </a>
                </li>
                <li class="nav-item">
                    <a href="laporan-penjualan.php" class="nav-link <?php echo ($current_page === 'laporan-penjualan.php') ? 'active' : ''; ?>">
                        <i class="bi bi-graph-up-arrow"></i> Laporan Penjualan
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($role === 'admin' || $role === 'gudang'): ?>
                <li class="nav-item">
                    <a href="buku.php" class="nav-link <?php echo ($current_page === 'buku.php') ? 'active' : ''; ?>">
                        <i class="bi bi-journal"></i> Data buku
                    </a>
                </li>
                <li class="nav-item">
                    <a href="pembelian.php" class="nav-link <?php echo ($current_page === 'pembelian.php') ? 'active' : ''; ?>">
                        <i class="bi bi-boxes"></i> Pembelian
                    </a>
                </li>
                <li class="nav-item">
                    <a href="laporan-pembelian.php" class="nav-link <?php echo ($current_page === 'laporan-pembelian.php') ? 'active' : ''; ?>">
                        <i class="bi bi-graph-down-arrow"></i> Laporan Pembelian
                    </a>
                </li>
                <li class="nav-item">
                    <a href="stok.php" class="nav-link <?php echo ($current_page === 'stok.php') ? 'active' : ''; ?>">
                        <i class="bi bi-clipboard-data"></i> Manajemen Stok
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($role === 'admin'): ?>
                <li class="nav-item">
                    <a href="kategori.php" class="nav-link <?php echo ($current_page === 'kategori.php') ? 'active' : ''; ?>">
                        <i class="bi bi-bookmarks"></i> Kategori buku
                    </a>
                </li>
                <li class="nav-item">
                    <a href="penerbit.php" class="nav-link <?php echo ($current_page === 'penerbit.php') ? 'active' : ''; ?>">
                        <i class="bi bi-buildings"></i> Penerbit
                    </a>
                </li>
                <li class="nav-item">
                    <a href="customer.php" class="nav-link <?php echo ($current_page === 'customer.php') ? 'active' : ''; ?>">
                        <i class="bi bi-person-plus"></i> Customer
                    </a>
                </li>
                <li class="nav-item">
                    <a href="users.php" class="nav-link <?php echo ($current_page === 'users.php') ? 'active' : ''; ?>">
                        <i class="bi bi-people"></i> Manajemen User
                    </a>
                </li>
                <li class="nav-item">
                    <a href="pengaturan.php" class="nav-link <?php echo ($current_page === 'pengaturan.php') ? 'active' : ''; ?>">
                        <i class="bi bi-gear"></i> Pengaturan
                    </a>
                </li>
            <?php endif; ?>

            <li class="nav-item">
                <a href="logout.php" class="nav-link <?php echo ($current_page === 'logout.php') ? 'active' : ''; ?>" style="color: red">
                    <i class="bi bi-power"></i> Logout
                </a>
            </li>
        </ul>
    </nav>
</body>